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
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
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

    /** If link objects by default should be included to response. */
    const INCLUDED = 'included';

    /** If meta information should be shown. */
    const SHOW_META = 'showMeta';

    /** If 'self' URL should be shown. Requires 'self' controller to be set. */
    const SHOW_SELF = 'showSelf';

    /** If 'related' URL should be shown. Requires 'related' controller to be set. */
    const SHOW_RELATED = 'related';

    /** If linkage information should be shown. */
    const SHOW_LINKAGE = 'showLinkage';

    /** If link pagination information should be shown. */
    const SHOW_PAGINATION = 'showPagination';

    /** Arbitrary data describing 'self' controller. */
    const SELF_CONTROLLER = 'selfController';

    /** Arbitrary data describing 'related' controller. */
    const RELATED_CONTROLLER = 'relatedController';

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
    protected $isShowSelfInIncluded = false;

    /**
     * @var bool
     */
    protected $isShowLinksInIncluded = false;

    /**
     * @var bool
     */
    protected $isShowMetaInIncluded = false;

    /**
     * @var int
     */
    protected $defaultParseDepth = null;

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
            'is_null($this->defaultParseDepth) || '.
            '(is_int($this->defaultParseDepth) && $this->defaultParseDepth > 0)',
            'If set depth should be positive int.'
        );

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
        return $this->getBaseSelfUrl($resource) . $this->getId($resource);
    }

    /**
     * Get the base self URL
     *
     * @param object $resource
     * @return string
     */
    protected function getBaseSelfUrl($resource)
    {
        assert('is_string($this->baseSelfUrl) && empty($this->baseSelfUrl) === false', 'Base \'self\' not set.');

        substr($this->baseSelfUrl, -1) === '/' ?: $this->baseSelfUrl .= '/';

        return $this->baseSelfUrl;
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
    public function isShowSelfInIncluded()
    {
        assert('is_bool($this->isShowSelfInIncluded)');
        return $this->isShowSelfInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowLinksInIncluded()
    {
        assert('is_bool($this->isShowLinksInIncluded)');
        return $this->isShowLinksInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowMetaInIncluded()
    {
        assert('is_bool($this->isShowMetaInIncluded)');
        return $this->isShowMetaInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultParseDepth()
    {
        return $this->defaultParseDepth;
    }

    /**
     * @inheritdoc
     */
    public function getLinks($resource)
    {
        $resource ?: null;
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinkObjectIterator($resource)
    {
        foreach ($this->getLinks($resource) as $name => $desc) {
            assert('is_string($name) === true && empty($name) === false');
            assert('is_array($desc) === true');

            $relatedController = $this->getNotEmptyValue($desc, self::RELATED_CONTROLLER);
            $selfController    = $this->getNotEmptyValue($desc, self::SELF_CONTROLLER);
            $data              = $this->readData($desc);
            $isIncluded        = ($this->getValue($desc, self::INCLUDED) === true);
            $isShowMeta        = ($this->getValue($desc, self::SHOW_META) === true);
            $isShowSelf        = $this->isShowControllerUrl($selfController, $desc, self::SHOW_SELF);
            $isShowAsRef       = $this->isShowControllerUrl($relatedController, $desc, self::SHOW_AS_REF);
            $isShowRelated     = $this->isShowControllerUrl($relatedController, $desc, self::SHOW_RELATED);
            $isShowLinkage     = ($this->getValue($desc, self::SHOW_LINKAGE, true) === true);

            list($isShowPagination, $pagination) = $this->readPagination($desc);

            $selfSubUrl    = $this->getValue($desc, self::SELF_SUB_URL, '/links/'.$name);
            $relatedSubUrl = $this->getValue($desc, self::RELATED_SUB_URL, '/'.$name);
            assert('is_string($selfSubUrl) && is_string($relatedSubUrl)');

            $selfSubUrl    = ($selfController    === null ? null : $selfSubUrl);
            $relatedSubUrl = ($relatedController === null ? null : $relatedSubUrl);

            assert('is_object($data) || is_array($data) || $data === null');

            yield $this->factory->createLinkObject(
                $name,
                $data,
                $selfSubUrl,
                $relatedSubUrl,
                $isShowAsRef,
                $isShowSelf,
                $isShowRelated,
                $isShowLinkage,
                $isShowMeta,
                $isShowPagination,
                $isIncluded,
                $selfController,
                $relatedController,
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
            $this->getSelfControllerData(),
            $this->isShowSelf,
            $this->isShowMeta,
            $this->isShowSelfInIncluded(),
            $this->isShowLinksInIncluded(),
            $this->isShowMetaInIncluded()
        );
    }

    /**
     * Get 'self' controller data.
     *
     * @return mixed
     */
    protected function getSelfControllerData()
    {
        return null;
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
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getNotEmptyValue(array $array, $key, $default = null)
    {
        if (isset($array[$key]) === true) {
            $value = $array[$key];
            if (empty($value) === false) {
                return $value;
            }
        }
        return $default;
    }

    /**
     * @param mixed  $controllerData
     * @param array  $description
     * @param string $showKey
     *
     * @return bool
     */
    private function isShowControllerUrl($controllerData, array $description, $showKey)
    {
        return ($controllerData !== null && $this->getValue($description, $showKey) === true);
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
