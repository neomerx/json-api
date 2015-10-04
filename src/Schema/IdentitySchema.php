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
use \LogicException;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class IdentitySchema extends SchemaProvider
{
    /**
     * @var Closure
     */
    private $identityClosure;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface     $container
     * @param string                 $classType
     * @param Closure                $identityClosure function($resource) : string
     */
    public function __construct(
        SchemaFactoryInterface $factory,
        ContainerInterface $container,
        $classType,
        Closure $identityClosure
    ) {
        $schemaForRealType  = $container->getSchemaByType($classType);
        $this->resourceType = $schemaForRealType->getResourceType();
        $this->selfSubUrl   = $schemaForRealType->getSelfSubUrl();

        parent::__construct($factory, $container);

        $this->identityClosure = $identityClosure;
    }

    /**
     * @inheritdoc
     */
    public function getId($resource)
    {
        $closure  = $this->identityClosure;
        $identity = $closure($resource);

        return $identity;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource)
    {
        // this method should not be called
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource, array $includeRelationships = [])
    {
        // this method should not be called
        throw new LogicException();
    }
}
