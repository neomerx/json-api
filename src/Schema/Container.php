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

        if (isset($this->createdProviders[$resourceType])) {
            return $this->createdProviders[$resourceType];
        }

        assert('isset($this->providerMapping[$resourceType])', 'Have you added provider for `'.$resourceType.'`?');

        $classNameOrClosure = $this->providerMapping[$resourceType];
        if ($classNameOrClosure instanceof Closure) {
            $this->createdProviders[$resourceType] = ($schema = $classNameOrClosure($this->factory, $this));
        } else {
            $this->createdProviders[$resourceType] = ($schema = new $classNameOrClosure($this->factory, $this));
        }

        return $schema;
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
