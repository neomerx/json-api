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
use \Neomerx\JsonApi\Contracts\Schema\FactoryInterface;
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

    /** Arbitrary data describing 'self' controller. */
    const SELF_CONTROLLER = 'selfController';

    /** Arbitrary data describing 'related' controller. */
    const RELATED_CONTROLLER = 'relatedController';

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
    protected $isShowSelfInIncluded = true;

    /**
     * @var bool
     */
    protected $isShowMetaInIncluded = true;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param FactoryInterface   $factory
     * @param ContainerInterface $container
     */
    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        assert('is_string($this->baseSelfUrl)  && empty($this->baseSelfUrl) === false', 'Base \'self\' not set.');
        assert('is_string($this->resourceType) && empty($this->resourceType) === false', 'Resource type not set.');

        substr($this->baseSelfUrl, -1) === '/' ?: $this->baseSelfUrl .= '/';

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
    public function getIds($resources)
    {
        if (is_array($resources) === false) {
            return [$this->getId($resources)];
        }

        $ids = [];
        foreach ($resources as $resource) {
            $ids[] = $this->getId($resource);
        }
        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl($resource)
    {
        return $this->baseSelfUrl . $this->getId($resource);
    }

    /**
     * @inheritdoc
     */
    public function getMeta($data)
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
    public function isShowMetaInIncluded()
    {
        assert('is_bool($this->isShowMetaInIncluded)');
        return $this->isShowMetaInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(/** @noinspection PhpUnusedParameterInspection */ $resource)
    {
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
            $data              = $this->getValue($desc, self::DATA);
            $isIncluded        = ($this->getValue($desc, self::INCLUDED) === true);
            $isShowMeta        = ($this->getValue($desc, self::SHOW_META) === true);
            $isShowSelf        = ($selfController    !== null && $this->getValue($desc, self::SHOW_SELF) === true);
            $isShowAsRef       = ($relatedController !== null && $this->getValue($desc, self::SHOW_AS_REF) === true);
            $isShowRelated     = ($relatedController !== null && $this->getValue($desc, self::SHOW_RELATED) === true);

            $selfSubUrl    = $this->getValue($desc, self::SELF_SUB_URL, '/links/'.$name);
            $relatedSubUrl = $this->getValue($desc, self::RELATED_SUB_URL, '/'.$name);
            assert('is_string($selfSubUrl) && is_string($relatedSubUrl)');

            $selfSubUrl    = ($selfController    === null ? null : $selfSubUrl);
            $relatedSubUrl = ($relatedController === null ? null : $relatedSubUrl);

            if ($data instanceof Closure) {
                $data = $data();
            }

            assert('is_object($data) || is_array($data) || $data === null');

            yield $this->factory->createLinkObject(
                $name,
                $data,
                $selfSubUrl,
                $relatedSubUrl,
                $isShowAsRef,
                $isShowSelf,
                $isShowRelated,
                $isShowMeta,
                $isIncluded,
                $selfController,
                $relatedController
            );
        }
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
        return (isset($array[$key]) === true && empty($value = $array[$key]) === false ? $value : $default);
    }
}
