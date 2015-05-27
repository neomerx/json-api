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
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class SchemaProvider implements SchemaProviderInterface
{
    /** Linked data key. */
    const DATA = 'data';

    /** If link should be shown as reference. */
    const SHOW_AS_REF = 'asRef';

    /** If meta information should be shown. */
    const SHOW_META = 'showMeta';

    /** If 'self' URL should be shown. Requires 'self' controller to be set. */
    const SHOW_SELF = 'showSelf';

    /** If 'related' URL should be shown. Requires 'related' controller to be set. */
    const SHOW_RELATED = 'related';

    /** If data should be shown in relationships. */
    const SHOW_DATA_IN_RELATIONSHIPS = 'showDataInRelationships';

    /** If link pagination information should be shown. */
    const SHOW_PAGINATION = 'showPagination';

    /** Link pagination information */
    const PAGINATION = 'pagination';

    /** Link pagination information */
    const PAGINATION_FIRST = 'first';

    /** Link pagination information */
    const PAGINATION_LAST = 'last';

    /** Link pagination information */
    const PAGINATION_PREV = 'prev';

    /** Link pagination information */
    const PAGINATION_NEXT = 'next';

    /** Default 'self' sub-URL could be changed with this key */
    const SELF_SUB_URL = 'selfSubUrl';

    /** Default 'related' sub-URL could be changed with this key */
    const RELATED_SUB_URL = 'relatedSubUrl';

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @var string
     */
    protected $baseSelfUrl;

    /**
     * @var bool
     */
    protected $isShowSelf = true;

    /**
     * @var bool
     */
    protected $isShowMeta = false;

    /**
     * @var bool
     */
    protected $isShowMetaInRelationships = false;

    /**
     * @var bool
     */
    protected $isShowSelfInIncluded = false;

    /**
     * @var bool
     */
    protected $isShowRelShipsInIncluded = false;

    /**
     * @var bool
     */
    protected $isShowMetaInIncluded = false;

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface     $container
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container)
    {
        assert('is_string($this->resourceType) && empty($this->resourceType) === false', 'Resource type not set.');
        assert(
            'is_bool($this->isShowSelfInIncluded) &&'.
            'is_bool($this->isShowRelShipsInIncluded) &&'.
            'is_bool($this->isShowMetaInIncluded)'
        );

        assert('is_string($this->baseSelfUrl) && empty($this->baseSelfUrl) === false', 'Base \'self\' not set.');

        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl($resource)
    {
        return $this->getBaseSelfUrl($resource).$this->getId($resource);
    }

    /**
     * @inheritdoc
     */
    public function getMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isShowSelf()
    {
        return $this->isShowSelf;
    }

    /**
     * @inheritdoc
     */
    public function isShowMeta()
    {
        return $this->isShowMeta;
    }

    /**
     * @inheritdoc
     */
    public function isShowSelfInIncluded()
    {
        return $this->isShowSelfInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowRelationshipsInIncluded()
    {
        return $this->isShowRelShipsInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowMetaInIncluded()
    {
        return $this->isShowMetaInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowMetaInRelationships()
    {
        return $this->isShowMetaInRelationships;
    }

    /**
     * Get resource links.
     *
     * @param object $resource
     *
     * @return array
     */
    public function getRelationships($resource)
    {
        $resource ?: null;
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipObjectIterator($resource)
    {
        foreach ($this->getRelationships($resource) as $name => $desc) {
            $data          = $this->readData($desc);
            $isShowMeta    = ($this->getValue($desc, self::SHOW_META, false) === true);
            $isShowSelf    = ($this->getValue($desc, self::SHOW_SELF, false) === true);
            $isShowAsRef   = ($this->getValue($desc, self::SHOW_AS_REF, false) === true);
            $isShowRelated = ($this->getValue($desc, self::SHOW_RELATED, false) === true);
            $isShowLinkage = ($this->getValue($desc, self::SHOW_DATA_IN_RELATIONSHIPS, true) === true);

            list($isShowPagination, $pagination) = $this->readPagination($desc);

            $selfLink    = $this->getSelfLink($name, $desc, $data);
            $relatedLink = $this->getRelatedLink($name, $desc, $data);

            yield $this->factory->createRelationshipObject(
                $name,
                $data,
                $selfLink,
                $relatedLink,
                $isShowAsRef,
                $isShowSelf,
                $isShowRelated,
                $isShowLinkage,
                $isShowMeta,
                $isShowPagination,
                $pagination
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject($resource, $isOriginallyArrayed, array $attributeKeysFilter = null)
    {
        $attributes = $this->getAttributes($resource);
        if ($attributeKeysFilter !== null) {
            $attributes = array_intersect_key($attributes, $attributeKeysFilter);
        }
        return $this->factory->createResourceObject(
            $isOriginallyArrayed,
            $this->getResourceType(),
            (string)$this->getId($resource),
            $attributes,
            $this->getMeta($resource),
            $this->getSelfUrl($resource),
            $this->isShowSelf(),
            $this->isShowMeta(),
            $this->isShowSelfInIncluded(),
            $this->isShowRelationshipsInIncluded(),
            $this->isShowMetaInIncluded(),
            $this->isShowMetaInRelationships()
        );
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        return [];
    }

    /**
     * Get the base self URL
     *
     * @param object $resource
     *
     * @return string
     */
    protected function getBaseSelfUrl($resource)
    {
        $resource ?: null;

        substr($this->baseSelfUrl, -1) === '/' ?: $this->baseSelfUrl .= '/';

        return $this->baseSelfUrl;
    }

    /**
     * Get link for 'self' relationship url.
     *
     * @param string            $relationshipName
     * @param array             $description
     * @param mixed             $relationshipData
     * @param null|array|object $meta
     *
     * @return LinkInterface
     */
    protected function getSelfLink($relationshipName, array $description, $relationshipData, $meta = null)
    {
        $relationshipData ?: null;
        $subHref = $this->getValue($description, self::SELF_SUB_URL, '/relationships/'.$relationshipName);
        return $this->factory->createLink($subHref, $meta);
    }

    /**
     * Get link for 'self' relationship url.
     *
     * @param string            $relationshipName
     * @param array             $description
     * @param mixed             $relationshipData
     * @param null|array|object $meta
     *
     * @return LinkInterface
     */
    protected function getRelatedLink($relationshipName, array $description, $relationshipData, $meta = null)
    {
        $relationshipData ?: null;
        $href = $this->getValue($description, self::RELATED_SUB_URL, '/'.$relationshipName);
        return $this->factory->createLink($href, $meta);
    }

    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getValue(array $array, $key, $default = null)
    {
        return (isset($array[$key]) === true ? $array[$key] : $default);
    }

    /**
     * @param array $description
     *
     * @return mixed
     */
    private function readData(array $description)
    {
        $data = $this->getValue($description, self::DATA);
        if ($data instanceof Closure) {
            $data = $data();
        }
        return $data;
    }

    /**
     * @param array $description
     *
     * @return array
     */
    private function readPagination(array $description)
    {
        $pagination       = null;
        $isShowPagination = $this->getValue($description, self::SHOW_PAGINATION, false);
        if (isset($description[self::PAGINATION]) === true) {
            $paginationData = $description[self::PAGINATION];
            $first = $this->getValue($paginationData, self::PAGINATION_FIRST);
            $last  = $this->getValue($paginationData, self::PAGINATION_LAST);
            $prev  = $this->getValue($paginationData, self::PAGINATION_PREV);
            $next  = $this->getValue($paginationData, self::PAGINATION_NEXT);
            if ($first !== null || $last !== null || $prev !== null || $next !== null) {
                $pagination = $this->factory->createPaginationLinks($first, $last, $prev, $next);
            }
        }

        $isShowPagination = ($isShowPagination === true && $pagination !== null);

        return [$isShowPagination, $pagination];
    }
}
