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

use EmptyIterator;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * @package Neomerx\JsonApi
 */
class ResourceIdentifierSchemaAdapter implements SchemaInterface
{
    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     * @param SchemaInterface  $schema
     */
    public function __construct(FactoryInterface $factory, SchemaInterface $schema)
    {
        $this->schema  = $schema;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return $this->schema->getResourceType();
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl($resource = null): string
    {
        return $this->schema->getSelfSubUrl($resource);
    }

    /**
     * @inheritdoc
     */
    public function getId($resource): ?string
    {
        return $this->schema->getId($resource);
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubLink($resource): LinkInterface
    {
        return $this->schema->getSelfSubLink($resource);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource, array $fieldKeysFilter = null): ?array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships(
        $resource,
        bool $isPrimary,
        array $includeRelationships,
        array $fieldKeysFilter = null
    ): ?array {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject(
        $resource,
        bool $isOriginallyArrayed,
        array $fieldKeysFilter = null
    ): ResourceObjectInterface {
        $fieldKeysFilter = [];

        return $this->factory->createResourceObject($this, $resource, $isOriginallyArrayed, $fieldKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipObjectIterator($resource, bool $isPrimary, array $includeRelationships): iterable
    {
        return new EmptyIterator();
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isShowAttributesInIncluded(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths(): array
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

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getRelationshipSelfLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface {
        return $this->schema->getRelationshipSelfLink($resource, $name, $meta, $treatAsHref);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getRelationshipRelatedLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface {
        return $this->schema->getRelationshipRelatedLink($resource, $name, $meta, $treatAsHref);
    }
}
