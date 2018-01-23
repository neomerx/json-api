<?php namespace Neomerx\JsonApi\Schema;

/**
 * Copyright 2015-2018 info@neomerx.com
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use Closure;
use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Factories\Exceptions;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function Neomerx\JsonApi\I18n\translate as _;

/**
 * @package Neomerx\JsonApi
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Message code.
     */
    const MSG_INVALID_TYPE = 0;

    /**
     * Message code.
     */
    const MSG_INVALID_SCHEME = self::MSG_INVALID_TYPE + 1;

    /**
     * Message code.
     */
    const MSG_TYPE_REUSE_FORBIDDEN = self::MSG_INVALID_SCHEME + 1;

    /**
     * Message code.
     */
    const MSG_UNREGISTERED_SCHEME_FOR_TYPE = self::MSG_TYPE_REUSE_FORBIDDEN + 1;

    /**
     * Message code.
     */
    const MSG_UNREGISTERED_SCHEME_FOR_RESOURCE_TYPE = self::MSG_UNREGISTERED_SCHEME_FOR_TYPE + 1;

    /**
     * Default messages.
     */
    const MESSAGES = [
        self::MSG_INVALID_TYPE                          => 'Type must be non-empty string.',
        self::MSG_INVALID_SCHEME                        =>
            'Schema for type \'%s\' must be non-empty string, callable or SchemaInterface instance.',
        self::MSG_TYPE_REUSE_FORBIDDEN                  =>
            'Type should not be used more than once to register a schema (\'%s\').',
        self::MSG_UNREGISTERED_SCHEME_FOR_TYPE          => 'Schema is not registered for type \'%s\'.',
        self::MSG_UNREGISTERED_SCHEME_FOR_RESOURCE_TYPE => 'Schema is not registered for resource type \'%s\'.',
    ];

    /**
     * @var array
     */
    private $providerMapping = [];

    /**
     * @var SchemaInterface[]
     */
    private $createdProviders = [];

    /**
     * @var array
     */
    private $resType2JsonType = [];

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $messages;

    /**
     * @param SchemaFactoryInterface $factory
     * @param iterable               $schemas
     * @param array                  $messages
     */
    public function __construct(SchemaFactoryInterface $factory, iterable $schemas = [], $messages = self::MESSAGES)
    {
        $this->factory  = $factory;
        $this->messages = $messages;
        $this->registerArray($schemas);
    }

    /**
     * Register provider for resource type.
     *
     * @param string         $type
     * @param string|Closure $schema
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function register(string $type, $schema): void
    {
        // Type must be non-empty string
        if (empty($type) === true) {
            throw new InvalidArgumentException(_($this->messages[self::MSG_INVALID_TYPE]));
        }

        $isOk = (
            (is_string($schema) === true && empty($schema) === false) ||
            is_callable($schema) ||
            $schema instanceof SchemaInterface
        );
        if ($isOk === false) {
            throw new InvalidArgumentException(_($this->messages[self::MSG_INVALID_SCHEME], $type));
        }

        if ($this->hasProviderMapping($type) === true) {
            throw new InvalidArgumentException(_($this->messages[self::MSG_TYPE_REUSE_FORBIDDEN], $type));
        }

        if ($schema instanceof SchemaInterface) {
            $this->setProviderMapping($type, get_class($schema));
            $this->setResourceToJsonTypeMapping($schema->getResourceType(), $type);
            $this->setCreatedProvider($type, $schema);
        } else {
            $this->setProviderMapping($type, $schema);
        }
    }

    /**
     * Register providers for resource types.
     *
     * @param iterable $schemas
     *
     * @return void
     */
    public function registerArray(iterable $schemas): void
    {
        foreach ($schemas as $type => $schema) {
            $this->register($type, $schema);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource): ?SchemaInterface
    {
        if ($resource === null) {
            return null;
        }

        $resourceType = $this->getResourceType($resource);

        return $this->getSchemaByType($resourceType);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getSchemaByType(string $type): SchemaInterface
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        if ($this->hasCreatedProvider($type) === true) {
            return $this->getCreatedProvider($type);
        }

        if ($this->hasProviderMapping($type) === false) {
            throw new InvalidArgumentException(_($this->messages[self::MSG_UNREGISTERED_SCHEME_FOR_TYPE], $type));
        }

        $classNameOrCallable = $this->getProviderMapping($type);
        if (is_string($classNameOrCallable) === true) {
            $schema = $this->createSchemaFromClassName($classNameOrCallable);
        } else {
            assert(is_callable($classNameOrCallable) === true);
            $schema = $this->createSchemaFromCallable($classNameOrCallable);
        }
        $this->setCreatedProvider($type, $schema);

        /** @var SchemaInterface $schema */

        $this->setResourceToJsonTypeMapping($schema->getResourceType(), $type);

        return $schema;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getSchemaByResourceType(string $resourceType): SchemaInterface
    {
        // Schema is not found among instantiated schemas for resource type $resourceType
        $isOk = (is_string($resourceType) === true && $this->hasResourceToJsonTypeMapping($resourceType) === true);

        // Schema might not be found if it hasn't been searched by type (not resource type) before.
        // We instantiate all schemas and then find one.
        if ($isOk === false) {
            foreach ($this->getProviderMappings() as $type => $schema) {
                if ($this->hasCreatedProvider($type) === false) {
                    // it will instantiate the schema
                    $this->getSchemaByType($type);
                }
            }
        }

        // search one more time
        $isOk = (is_string($resourceType) === true && $this->hasResourceToJsonTypeMapping($resourceType) === true);

        if ($isOk === false) {
            throw new InvalidArgumentException(_(
                $this->messages[self::MSG_UNREGISTERED_SCHEME_FOR_RESOURCE_TYPE],
                $resourceType
            ));
        }

        return $this->getSchemaByType($this->getJsonType($resourceType));
    }

    /**
     * @return SchemaFactoryInterface
     */
    protected function getFactory(): SchemaFactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return array
     */
    protected function getProviderMappings(): array
    {
        return $this->providerMapping;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping(string $type): bool
    {
        return array_key_exists($type, $this->providerMapping);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getProviderMapping(string $type)
    {
        return $this->providerMapping[$type];
    }

    /**
     * @param string         $type
     * @param string|Closure $schema
     *
     * @return void
     */
    protected function setProviderMapping(string $type, $schema): void
    {
        $this->providerMapping[$type] = $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasCreatedProvider(string $type): bool
    {
        return array_key_exists($type, $this->createdProviders);
    }

    /**
     * @param string $type
     *
     * @return SchemaInterface
     */
    protected function getCreatedProvider(string $type): SchemaInterface
    {
        return $this->createdProviders[$type];
    }

    /**
     * @param string          $type
     * @param SchemaInterface $provider
     *
     * @return void
     */
    protected function setCreatedProvider(string $type, SchemaInterface $provider): void
    {
        $this->createdProviders[$type] = $provider;
    }

    /**
     * @param string $resourceType
     *
     * @return bool
     */
    protected function hasResourceToJsonTypeMapping(string $resourceType): bool
    {
        return array_key_exists($resourceType, $this->resType2JsonType);
    }

    /**
     * @param string $resourceType
     *
     * @return string
     */
    protected function getJsonType(string $resourceType): string
    {
        return $this->resType2JsonType[$resourceType];
    }

    /**
     * @param string $resourceType
     * @param string $jsonType
     *
     * @return void
     */
    protected function setResourceToJsonTypeMapping(string $resourceType, string $jsonType): void
    {
        $this->resType2JsonType[$resourceType] = $jsonType;
    }

    /**
     * @param object $resource
     *
     * @return string
     */
    protected function getResourceType($resource): string
    {
        return get_class($resource);
    }

    /**
     * @param callable $callable
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromCallable(callable $callable): SchemaInterface
    {
        $schema = call_user_func($callable, $this->getFactory());

        return $schema;
    }

    /**
     * @param string $className
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromClassName(string $className): SchemaInterface
    {
        $schema = new $className($this->getFactory());

        return $schema;
    }
}
