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

use \EmptyIterator;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
class ResourceIdentifierSchemaAdapter implements SchemaProviderInterface
{
    /**
     * @var SchemaProviderInterface
     */
    private $schema;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface        $factory
     * @param SchemaProviderInterface $schema
     */
    public function __construct(FactoryInterface $factory, SchemaProviderInterface $schema)
    {
        $this->schema  = $schema;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->schema->getResourceType();
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl()
    {
        return $this->schema->getSelfSubUrl();
    }

    /**
     * @inheritdoc
     */
    public function getId($resource)
    {
        return $this->schema->getId($resource);
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubLink($resource)
    {
        return $this->schema->getSelfSubLink($resource);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject($resource, $isOriginallyArrayed, $attributeKeysFilter = null)
    {
        $attributeKeysFilter = [];
        return $this->factory->createResourceObject($this, $resource, $isOriginallyArrayed, $attributeKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipObjectIterator($resource, array $includeRelationships = [])
    {
        return new EmptyIterator();
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isShowAttributesInIncluded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isShowRelationshipsInIncluded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta($resource)
    {
        return $this->schema->getPrimaryMeta($resource);
    }

    /**
     * @inheritdoc
     */
    public function getInclusionMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsPrimaryMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsInclusionMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getLinkageMeta($resource)
    {
        return $this->schema->getLinkageMeta($resource);
    }
}
