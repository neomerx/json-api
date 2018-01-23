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

use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
abstract class BaseSchema implements SchemaInterface
{
    /** Links information */
    const LINKS = DocumentInterface::KEYWORD_LINKS;

    /** Linked data key. */
    const DATA = DocumentInterface::KEYWORD_DATA;

    /** Relationship meta */
    const META = DocumentInterface::KEYWORD_META;

    /** If 'self' URL should be shown. */
    const SHOW_SELF = 'showSelf';

    /** If 'related' URL should be shown. */
    const SHOW_RELATED = 'related';

    /** If data should be shown in relationships. */
    const SHOW_DATA = 'showData';

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @var string Must start with '/' e.g. '/sub-url'
     */
    protected $selfSubUrl;

    /**
     * @var bool
     */
    protected $isShowAttributesInIncluded = true;

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @param SchemaFactoryInterface $factory
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function __construct(SchemaFactoryInterface $factory)
    {
        assert(
            is_string($this->getResourceType()) === true && empty($this->getResourceType()) === false,
            'Resource type is not set for Schema \'' . static::class . '\'.'
        );

        if ($this->selfSubUrl === null) {
            $this->selfSubUrl = '/' . $this->getResourceType();
        } else {
            assert(
                is_string($this->selfSubUrl) === true && empty($this->selfSubUrl) === false &&
                $this->selfSubUrl[0] === '/' && $this->selfSubUrl[strlen($this->selfSubUrl) - 1] != '/',
                '\'Self\' sub-url set incorrectly for Schema \'' . static::class . '\'.'
            );
        }

        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl($resource = null): string
    {
        return $resource === null ? $this->selfSubUrl : $this->selfSubUrl . '/' . $this->getId($resource);
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubLink($resource): LinkInterface
    {
        return $this->createLink($this->getSelfSubUrl($resource));
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
        $link = $this->createLink($this->getRelationshipSelfUrl($resource, $name), $meta, $treatAsHref);

        return $link;
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
        $link = $this->createLink($this->getRelationshipRelatedUrl($resource, $name), $meta, $treatAsHref);

        return $link;
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getLinkageMeta($resource)
    {
        return null;
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
    public function isShowAttributesInIncluded(): bool
    {
        return $this->isShowAttributesInIncluded;
    }

    /**
     * Get resource links.
     *
     * @param object     $resource
     * @param bool       $isPrimary
     * @param array      $includeRelationships A list of relationships that will be included as full resources.
     *
     * @return array
     */
    public function getRelationships($resource, bool $isPrimary, array $includeRelationships): ?array
    {
        assert($resource || $isPrimary || $includeRelationships || true);

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
        return $this->factory->createResourceObject($this, $resource, $isOriginallyArrayed, $fieldKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipObjectIterator($resource, bool $isPrimary, array $includeRelationships): iterable
    {
        $relationships = $this->getRelationships($resource, $isPrimary, $includeRelationships);
        foreach ($relationships as $name => $desc) {
            yield $this->createRelationshipObject($resource, $name, $desc);
        }
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
    public function getResourceLinks($resource): array
    {
        $links = [
            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource): array
    {
        return [];
    }

    /**
     * @param object $resource
     * @param string $name
     *
     * @return string
     */
    protected function getRelationshipSelfUrl($resource, $name)
    {
        $url = $this->getSelfSubUrl($resource) . '/' . DocumentInterface::KEYWORD_RELATIONSHIPS . '/' . $name;

        return $url;
    }

    /**
     * @param object $resource
     * @param string $name
     *
     * @return string
     */
    protected function getRelationshipRelatedUrl($resource, $name)
    {
        $url = $this->getSelfSubUrl($resource) . '/' . $name;

        return $url;
    }

    /**
     * @param string     $subHref
     * @param null|mixed $meta
     * @param bool       $treatAsHref
     *
     * @return LinkInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function createLink($subHref, $meta = null, $treatAsHref = false)
    {
        return $this->factory->createLink($subHref, $meta, $treatAsHref);
    }

    /**
     * @param object $resource
     * @param string $relationshipName
     * @param array  $description
     * @param bool   $isShowSelf
     * @param bool   $isShowRelated
     *
     * @return array <string,LinkInterface>
     */
    protected function readLinks($resource, $relationshipName, array $description, $isShowSelf, $isShowRelated)
    {
        $links = $description[self::LINKS] ?? [];
        if ($isShowSelf === true && isset($links[LinkInterface::SELF]) === false) {
            $links[LinkInterface::SELF] = $this->getRelationshipSelfLink($resource, $relationshipName);
        }
        if ($isShowRelated === true && isset($links[LinkInterface::RELATED]) === false) {
            $links[LinkInterface::RELATED] = $this->getRelationshipRelatedLink($resource, $relationshipName);
        }

        return $links;
    }

    /**
     * @param object $resource
     * @param string $name
     * @param array  $desc
     *
     * @return RelationshipObjectInterface
     */
    protected function createRelationshipObject($resource, $name, array $desc)
    {
        $data          = $desc[self::DATA] ?? null;
        $meta          = $desc[self::META] ?? null;
        $isShowSelf    = (($desc[self::SHOW_SELF] ?? false) === true);
        $isShowRelated = (($desc[self::SHOW_RELATED] ?? false) === true);
        $isShowData    = (($desc[self::SHOW_DATA] ?? array_key_exists(self::DATA, $desc)) === true);
        $links         = $this->readLinks($resource, $name, $desc, $isShowSelf, $isShowRelated);

        return $this->factory->createRelationshipObject($name, $data, $links, $meta, $isShowData, false);
    }
}
