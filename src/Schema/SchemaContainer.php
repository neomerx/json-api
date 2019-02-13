<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Schema;

/**
 * Copyright 2015-2019 info@neomerx.com
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
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use function Neomerx\JsonApi\I18n\format as _;

/**
 * @package Neomerx\JsonApi
 */
class SchemaContainer implements SchemaContainerInterface
{
    /**
     * Message code.
     */
    const MSG_INVALID_MODEL_TYPE = 'Invalid model type.';

    /**
     * Message code.
     */
    const MSG_INVALID_SCHEME = 'Schema for type `%s` must be non-empty string, callable or SchemaInterface instance.';

    /**
     * Message code.
     */
    const MSG_TYPE_REUSE_FORBIDDEN = 'Type should not be used more than once to register a schema (`%s`).';

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
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     * @param iterable         $schemas
     */
    public function __construct(FactoryInterface $factory, iterable $schemas)
    {
        $this->factory = $factory;
        $this->registerCollection($schemas);
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function register(string $type, $schema): void
    {
        if (empty($type) === true || \class_exists($type) === false) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_MODEL_TYPE));
        }

        $isOk = (
            (
                \is_string($schema) === true &&
                empty($schema) === false &&
                \class_exists($schema) === true &&
                \in_array(SchemaInterface::class, \class_implements($schema)) === true
            ) ||
            \is_callable($schema) ||
            $schema instanceof SchemaInterface
        );
        if ($isOk === false) {
            throw new InvalidArgumentException(_(static::MSG_INVALID_SCHEME, $type));
        }

        if ($this->hasProviderMapping($type) === true) {
            throw new InvalidArgumentException(_(static::MSG_TYPE_REUSE_FORBIDDEN, $type));
        }

        if ($schema instanceof SchemaInterface) {
            $this->setProviderMapping($type, \get_class($schema));
            $this->setResourceToJsonTypeMapping($schema->getType(), $type);
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
    public function registerCollection(iterable $schemas): void
    {
        foreach ($schemas as $type => $schema) {
            $this->register($type, $schema);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource): SchemaInterface
    {
        \assert($this->hasSchema($resource));

        $resourceType = $this->getResourceType($resource);

        return $this->getSchemaByType($resourceType);
    }

    /**
     * @inheritdoc
     */
    public function hasSchema($resourceObject): bool
    {
        return \is_object($resourceObject) === true &&
            $this->hasProviderMapping($this->getResourceType($resourceObject)) === true;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    protected function getSchemaByType(string $type): SchemaInterface
    {
        if ($this->hasCreatedProvider($type) === true) {
            return $this->getCreatedProvider($type);
        }

        $classNameOrCallable = $this->getProviderMapping($type);
        if (\is_string($classNameOrCallable) === true) {
            $schema = $this->createSchemaFromClassName($classNameOrCallable);
        } else {
            \assert(\is_callable($classNameOrCallable) === true);
            $schema = $this->createSchemaFromCallable($classNameOrCallable);
        }
        $this->setCreatedProvider($type, $schema);

        /** @var SchemaInterface $schema */

        $this->setResourceToJsonTypeMapping($schema->getType(), $type);

        return $schema;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasProviderMapping(string $type): bool
    {
        return isset($this->providerMapping[$type]);
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
        return isset($this->createdProviders[$type]);
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
        \assert(
            \is_object($resource) === true,
            'Unable to get a type of the resource as it is not an object.'
        );

        return \get_class($resource);
    }

    /**
     * @param callable $callable
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromCallable(callable $callable): SchemaInterface
    {
        $schema = \call_user_func($callable, $this->factory);

        return $schema;
    }

    /**
     * @param string $className
     *
     * @return SchemaInterface
     */
    protected function createSchemaFromClassName(string $className): SchemaInterface
    {
        $schema = new $className($this->factory);

        return $schema;
    }
}
