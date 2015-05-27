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

use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
class ResourceObject implements ResourceObjectInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $idx;

    /**
     * @var array
     */
    private $attributes;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @var bool
     */
    private $isShowSelf;

    /**
     * @var string
     */
    private $selfUrl;

    /**
     * @var bool
     */
    private $isShowMeta;

    /**
     * @var bool
     */
    private $isShowSelfInIncluded;

    /**
     * @var bool
     */
    private $isShowRelationshipsInIncluded;

    /**
     * @var bool
     */
    private $isShowMetaInIncluded;

    /**
     * @var bool
     */
    private $isShowMetaInRlShips;

    /**
     * @var bool
     */
    private $isInArray;

    /**
     * @param bool   $isInArray
     * @param string $type
     * @param string $idx
     * @param array  $attributes
     * @param mixed  $meta
     * @param string $selfUrl
     * @param bool   $isShowSelf
     * @param bool   $isShowMeta
     * @param bool   $isShowSelfInIncluded
     * @param bool   $isShowRelShipsInIncluded
     * @param bool   $isShowMetaInIncluded
     * @param bool   $isShowMetaInRlShips
     */
    public function __construct(
        $isInArray,
        $type,
        $idx,
        array $attributes,
        $meta,
        $selfUrl,
        $isShowSelf,
        $isShowMeta,
        $isShowSelfInIncluded,
        $isShowRelShipsInIncluded,
        $isShowMetaInIncluded,
        $isShowMetaInRlShips
    ) {
        assert(
            'is_bool($isInArray) && is_string($type) && is_string($idx) && is_array($attributes) &&'.
            'is_string($selfUrl) && is_bool($isShowSelf) && is_bool($isShowMeta) && is_bool($isShowMetaInRlShips) &&'.
            'is_bool($isShowSelfInIncluded) && is_bool($isShowRelShipsInIncluded) && is_bool($isShowMetaInIncluded)'
        );

        $this->isInArray                     = $isInArray;
        $this->type                          = $type;
        $this->idx                           = $idx;
        $this->attributes                    = $attributes;
        $this->meta                          = $meta;
        $this->isShowSelf                    = $isShowSelf;
        $this->selfUrl                       = $selfUrl;
        $this->isShowMeta                    = $isShowMeta;
        $this->isShowSelfInIncluded          = $isShowSelfInIncluded;
        $this->isShowRelationshipsInIncluded = $isShowRelShipsInIncluded;
        $this->isShowMetaInIncluded          = $isShowMetaInIncluded;
        $this->isShowMetaInRlShips           = $isShowMetaInRlShips;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idx;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl()
    {
        return $this->selfUrl;
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
    public function isShowMetaInIncluded()
    {
        return $this->isShowMetaInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isShowMetaInRelationships()
    {
        return $this->isShowMetaInRlShips;
    }

    /**
     * @inheritdoc
     */
    public function isShowRelationshipsInIncluded()
    {
        return $this->isShowRelationshipsInIncluded;
    }

    /**
     * @inheritdoc
     */
    public function isInArray()
    {
        return $this->isInArray;
    }
}
