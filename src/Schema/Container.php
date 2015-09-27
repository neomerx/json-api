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
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
class Container implements ContainerInterface
{
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
     * @param string         $resourceType
     * @param string|Closure $schema
     *
     * @return void
     */
    public function register($resourceType, $schema)
    {
        // Resource type must be non-empty string
        $isOk = (is_string($resourceType) === true && empty($resourceType) === false);
        $isOk ?: Exceptions::throwInvalidArgument('resourceType');

        // Schema must be non-empty string or Closure
        $isOk = ((is_string($schema) === true && empty($schema) === false) || $schema instanceof Closure);
        $isOk ?: Exceptions::throwInvalidArgument('schema');

        // Resource type should not be used more than once to register a schema
        isset($this->providerMapping[$resourceType]) === false ?: Exceptions::throwInvalidArgument('resourceType');

        $this->providerMapping[$resourceType] = $schema;
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
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type');

        if (isset($this->createdProviders[$type])) {
            return $this->createdProviders[$type];
        }

        // Schema is not registered for type $type
        isset($this->providerMapping[$type]) === true ?: Exceptions::throwInvalidArgument('type');

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
        // Schema is not registered for resource type $resourceType
        $isOk = (is_string($resourceType) === true && isset($this->resourceType2Type[$resourceType]) === true);
        $isOk ?: Exceptions::throwInvalidArgument('resourceType');

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
