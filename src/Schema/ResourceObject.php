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

use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Factories\Exceptions;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ResourceObject implements ResourceObjectInterface
{
    /**
     * @var string|false
     */
    private $idx = false;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var mixed
     */
    private $primaryMeta;

    /**
     * @var bool
     */
    private $isPrimaryMetaSet = false;

    /**
     * @var LinkInterface
     */
    private $selfSubLink;

    /**
     * @var bool
     */
    private $isInArray;

    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var object
     */
    protected $resource;

    /**
     * @var array<string,int>|null
     */
    protected $fieldKeysFilter;

    /**
     * @var bool
     */
    private $isSelfSubLinkSet = false;

    /**
     * @var bool
     */
    private $isRelationshipMetaSet = false;

    /**
     * @var mixed
     */
    private $relationshipMeta;

    /**
     * @var bool
     */
    private $isInclusionMetaSet = false;

    /**
     * @var mixed
     */
    private $inclusionMeta;

    /**
     * @var bool
     */
    private $isRelPrimaryMetaSet = false;

    /**
     * @var mixed
     */
    private $relPrimaryMeta;

    /**
     * @var bool
     */
    private $isRelIncMetaSet = false;

    /**
     * @var mixed
     */
    private $relInclusionMeta;

    /**
     * @param SchemaInterface $schema
     * @param object          $resource
     * @param bool            $isInArray
     * @param array<string,int>|null  $attributeKeysFilter
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        SchemaInterface $schema,
        $resource,
        bool $isInArray,
        array $fieldKeysFilter = null
    ) {
        is_object($resource) === true ?: Exceptions::throwInvalidArgument('resource', $resource);

        $this->schema          = $schema;
        $this->resource        = $resource;
        $this->isInArray       = $isInArray;
        $this->fieldKeysFilter = $fieldKeysFilter;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->schema->getResourceType();
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?string
    {
        if ($this->idx === false) {
            $index     = $this->schema->getId($this->resource);
            $this->idx = $index === null ? $index : (string)$index;
        }

        return $this->idx;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): ?array
    {
        if ($this->attributes === null) {
            $attributes = $this->schema->getAttributes($this->resource, $this->fieldKeysFilter);
            if ($this->fieldKeysFilter !== null) {
                $attributes = array_intersect_key($attributes, $this->fieldKeysFilter);
            }
            $this->attributes = $attributes;
        }

        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubLink(): LinkInterface
    {
        if ($this->isSelfSubLinkSet === false) {
            $this->selfSubLink      = $this->schema->getSelfSubLink($this->resource);
            $this->isSelfSubLinkSet = true;
        }

        return $this->selfSubLink;
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks(): array
    {
        return $this->schema->getResourceLinks($this->resource);
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks(): array
    {
        return $this->schema->getIncludedResourceLinks($this->resource);
    }

    /**
     * @inheritdoc
     */
    public function isShowAttributesInIncluded(): bool
    {
        return $this->schema->isShowAttributesInIncluded();
    }

    /**
     * @inheritdoc
     */
    public function isInArray(): bool
    {
        return $this->isInArray;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta()
    {
        if ($this->isPrimaryMetaSet === false) {
            $this->primaryMeta      = $this->schema->getPrimaryMeta($this->resource);
            $this->isPrimaryMetaSet = true;
        }

        return $this->primaryMeta;
    }

    /**
     * @inheritdoc
     */
    public function getInclusionMeta()
    {
        if ($this->isInclusionMetaSet === false) {
            $this->inclusionMeta      = $this->schema->getInclusionMeta($this->resource);
            $this->isInclusionMetaSet = true;
        }

        return $this->inclusionMeta;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsPrimaryMeta()
    {
        if ($this->isRelPrimaryMetaSet === false) {
            $this->relPrimaryMeta      = $this->schema->getRelationshipsPrimaryMeta($this->resource);
            $this->isRelPrimaryMetaSet = true;
        }

        return $this->relPrimaryMeta;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsInclusionMeta()
    {
        if ($this->isRelIncMetaSet === false) {
            $this->relInclusionMeta = $this->schema->getRelationshipsInclusionMeta($this->resource);
            $this->isRelIncMetaSet  = true;
        }

        return $this->relInclusionMeta;
    }

    /**
     * @inheritdoc
     */
    public function getLinkageMeta()
    {
        if ($this->isRelationshipMetaSet === false) {
            $this->relationshipMeta      = $this->schema->getLinkageMeta($this->resource);
            $this->isRelationshipMetaSet = true;
        }

        return $this->relationshipMeta;
    }
}
