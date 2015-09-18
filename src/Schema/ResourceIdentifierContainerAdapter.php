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

use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
class ResourceIdentifierContainerAdapter implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * ResourceIdentifierContainerAdapter constructor.
     *
     * @param FactoryInterface   $factory
     * @param ContainerInterface $container
     */
    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory   = $factory;
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resourceObject)
    {
        return $this->getSchemaAdapter($this->container->getSchema($resourceObject));
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByType($type)
    {
        return $this->getSchemaAdapter($this->container->getSchemaByType($type));
    }

    /**
     * @inheritdoc
     */
    public function getSchemaByResourceType($resourceType)
    {
        return $this->getSchemaAdapter($this->container->getSchemaByResourceType($resourceType));
    }

    /**
     * @param SchemaProviderInterface $schema
     *
     * @return SchemaProviderInterface
     */
    protected function getSchemaAdapter(SchemaProviderInterface $schema)
    {
        return $this->factory->createResourceIdentifierSchemaAdapter($schema);
    }
}
