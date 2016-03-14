<?php namespace Neomerx\JsonApi\Schema;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
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
     * @var array
     */
    private $createdProviders = [];

    /**
     * @var array
     */
    private $resourceType2Type = [];

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
     */
    public function register($type, $schema)
    {
        // Type must be non-empty string
        $isOk = (is_string($type) === true && empty($type) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t('Type must be non-empty string.'));
        }

        $isOk = ((is_string($schema) === true && empty($schema) === false) || $schema instanceof Closure);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                'Schema for type \'%s\' must be non-empty string or Closure.',
                [$type]
            ));
        }

        if (isset($this->providerMapping[$type]) === true) {
            throw new InvalidArgumentException(T::t(
                'Type should not be used more than once to register a schema (\'%s\').',
                [$type]
            ));
        }

        $this->providerMapping[$type] = $schema;
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
        $resourceType = $this->getResourceType($resource);

        return $this->getSchemaByType($resourceType);
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByType($type)
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        if (isset($this->createdProviders[$type])) {
            return $this->createdProviders[$type];
        }

        if (isset($this->providerMapping[$type]) === false) {
            throw new InvalidArgumentException(T::t('Schema is not registered for type \'%s\'.', [$type]));
        }

        $classNameOrClosure = $this->providerMapping[$type];
        if ($classNameOrClosure instanceof Closure) {
            $this->createdProviders[$type] = ($schema = $classNameOrClosure($this->factory, $this));
        } else {
            $this->createdProviders[$type] = ($schema = new $classNameOrClosure($this->factory, $this));
        }

        /** @var SchemaProviderInterface $schema */

        $this->resourceType2Type[$schema->getResourceType()] = $type;

        return $schema;
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByResourceType($resourceType)
    {
        // Schema is not found among instantiated schemas for resource type $resourceType
        $isOk = (is_string($resourceType) === true && isset($this->resourceType2Type[$resourceType]) === true);

        // Schema might not be found if it hasn't been searched by type (not resource type) before.
        // We instantiate all schemas and then find one.
        if ($isOk === false) {
            foreach ($this->providerMapping as $type => $schema) {
                if (isset($this->createdProviders[$type]) === false) {
                    // it will instantiate the schema
                    $this->getSchemaByType($type);
                }
            }
        }

        // search one more time
        $isOk = (is_string($resourceType) === true && isset($this->resourceType2Type[$resourceType]) === true);

        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                'Schema is not registered for resource type \'%s\'.',
                [$resourceType]
            ));
        }

        return $this->getSchemaByType($this->resourceType2Type[$resourceType]);
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
}
