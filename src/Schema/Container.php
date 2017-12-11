<?php namespace Neomerx\JsonApi\Schema;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \Closure;
use \InvalidArgumentException;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\I18n\Translator as T;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
class Container implements ContainerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $providerMapping = [];

    /**
     * @var SchemaProviderInterface[]
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
     * @param SchemaFactoryInterface $factory
     * @param array                  $schemas
     */
    public function __construct(SchemaFactoryInterface $factory, array $schemas = [])
    {
        $this->factory = $factory;
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
    public function register($type, $schema)
    {
        // Type must be non-empty string
        $isOk = (is_string($type) === true && empty($type) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t('Type must be non-empty string.'));
        }

        $isOk = (
            (is_string($schema) === true && empty($schema) === false) ||
            is_callable($schema) ||
            $schema instanceof SchemaProviderInterface
        );
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                'Schema for type \'%s\' must be non-empty string, callable or SchemaProviderInterface instance.',
                [$type]
            ));
        }

        if ($this->hasProviderMapping($type) === true) {
            throw new InvalidArgumentException(T::t(
                'Type should not be used more than once to register a schema (\'%s\').',
                [$type]
            ));
        }

        if ($schema instanceof SchemaProviderInterface) {
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
     * @param array $schemas
     *
     * @return void
     */
    public function registerArray(array $schemas)
    {
        foreach ($schemas as $type => $schema) {
            $this->register($type, $schema);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource)
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
    public function getSchemaByType($type)
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        if ($this->hasCreatedProvider($type) === true) {
            return $this->getCreatedProvider($type);
        }

        if ($this->hasProviderMapping($type) === false) {
            throw new InvalidArgumentException(T::t('Schema is not registered for type \'%s\'.', [$type]));
        }

        $classNameOrCallable = $this->getProviderMapping($type);
        if (is_string($classNameOrCallable) === true) {
            $schema = $this->createSchemaFromClassName($classNameOrCallable);
        } else {
            $schema = $this->createSchemaFromCallable($classNameOrCallable);
        }
        $this->setCreatedProvider($type, $schema);

        /** @var SchemaProviderInterface $schema */

        $this->setResourceToJsonTypeMapping($schema->getResourceType(), $type);

        return $schema;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getSchemaByResourceType($resourceType)
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
            throw new InvalidArgumentException(T::t(
                'Schema is not registered for resource type \'%s\'.',
                [$resourceType]
            ));
        }

        return $this->getSchemaByType($this->getJsonType($resourceType));
    }

    /**
     * @return SchemaFactoryInterface
     */
    protected function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return array
     */
    protected function getProviderMappings()
    {
        return $this->providerMapping;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping($type)
    {
        return array_key_exists($type, $this->providerMapping);
    }

    /**
     * @param string $type
     *
     * @return mixed
     */
    protected function getProviderMapping($type)
    {
        return $this->providerMapping[$type];
    }

    /**
     * @param string         $type
     * @param string|Closure $schema
     *
     * @return void
     */
    protected function setProviderMapping($type, $schema)
    {
        $this->providerMapping[$type] = $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasCreatedProvider($type)
    {
        return array_key_exists($type, $this->createdProviders);
    }

    /**
     * @param string $type
     *
     * @return SchemaProviderInterface
     */
    protected function getCreatedProvider($type)
    {
        return $this->createdProviders[$type];
    }

    /**
     * @param string                  $type
     * @param SchemaProviderInterface $provider
     *
     * @return void
     */
    protected function setCreatedProvider($type, SchemaProviderInterface $provider)
    {
        $this->createdProviders[$type] = $provider;
    }

    /**
     * @param string $resourceType
     *
     * @return bool
     */
    protected function hasResourceToJsonTypeMapping($resourceType)
    {
        return array_key_exists($resourceType, $this->resType2JsonType);
    }

    /**
     * @param string $resourceType
     *
     * @return string
     */
    protected function getJsonType($resourceType)
    {
        return $this->resType2JsonType[$resourceType];
    }

    /**
     * @param string $resourceType
     * @param string $jsonType
     *
     * @return void
     */
    protected function setResourceToJsonTypeMapping($resourceType, $jsonType)
    {
        $this->resType2JsonType[$resourceType] = $jsonType;
    }

    /**
     * @param object $resource
     *
     * @return string
     */
    protected function getResourceType($resource)
    {
        return get_class($resource);
    }

    /**
     * @deprecated Use `createSchemaFromCallable` method instead.
     * @param Closure $closure
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromClosure(Closure $closure)
    {
        $schema = $closure($this->getFactory());

        return $schema;
    }

    /**
     * @param callable $callable
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromCallable(callable $callable)
    {
        $schema = $callable instanceof Closure ?
            $this->createSchemaFromClosure($callable) : call_user_func($callable, $this->getFactory());

        return $schema;
    }

    /**
     * @param string $className
     *
     * @return SchemaProviderInterface
     */
    protected function createSchemaFromClassName($className)
    {
        $schema = new $className($this->getFactory());

        return $schema;
    }
}
