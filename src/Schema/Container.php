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
     * @param string        $resourceType
     * @param string|object $schema
     *
     * @return void
     */
    public function register($resourceType, $schema)
    {
        assert(
            'is_string($resourceType) && empty($resourceType) === false &&'.
            'isset($this->providerMapping[$resourceType]) === false &&'.
            '((is_string($schema) && empty($schema) === false) || $schema instanceof '. Closure::class . ')'
        );

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
        if (isset($this->createdProviders[$type])) {
            return $this->createdProviders[$type];
        }

        assert('isset($this->providerMapping[$type])', 'Have you added Schema for `'.$type.'`?');

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
        assert(
            'isset($this->resourceType2Type[$resourceType])',
            'Have you added Schema for resource type `'.$resourceType.'`?'
        );

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
