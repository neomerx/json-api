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

use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
class RelationshipObject implements RelationshipObjectInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var object|array|null
     */
    private $data;

    /**
     * @var array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    private $links;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @var bool
     */
    private $isShowSelf;

    /**
     * @var bool
     */
    private $isShowRelated;

    /**
     * @var bool
     */
    private $isShowMeta;

    /**
     * @var bool
     */
    private $isShowData;

    /**
     * @param string                      $name
     * @param object|array|null           $data
     * @param array<string,LinkInterface> $links
     * @param mixed                       $meta
     * @param bool                        $isShowSelf
     * @param bool                        $isShowRelated
     * @param bool                        $isShowMeta
     * @param bool                        $isShowData
     */
    public function __construct(
        $name,
        $data,
        $links,
        $meta,
        $isShowSelf,
        $isShowRelated,
        $isShowMeta,
        $isShowData
    ) {
        assert(
            'is_string($name) &&'.
            '(is_object($data) || is_array($data) || is_null($data)) &&'.
            'is_bool($isShowSelf) && is_bool($isShowRelated) && is_bool($isShowMeta) && is_array($links)'
        );
        assert(
            '$isShowSelf || $isShowRelated || $isShowData || $isShowMeta',
            'Specification requires at least one of them to be shown'
        );

        $this->name              = $name;
        $this->data              = $data;
        $this->links             = $links;
        $this->meta              = $meta;
        $this->isShowSelf        = $isShowSelf;
        $this->isShowRelated     = $isShowRelated;
        $this->isShowMeta        = $isShowMeta;
        $this->isShowData        = $isShowData;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        return $this->links;
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
    public function isShowSelf()
    {
        return $this->isShowSelf;
    }

    /**
     * @inheritdoc
     */
    public function isShowRelated()
    {
        return $this->isShowRelated;
    }

    /**
     * @inheritdoc
     */
    public function isShowData()
    {
        return $this->isShowData;
    }

    /**
     * @inheritdoc
     */
    public function isShowMeta()
    {
        return $this->isShowMeta;
    }
}
