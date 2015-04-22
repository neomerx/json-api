<?php namespace Neomerx\JsonApi\Document;

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

use \Iterator;
use \Neomerx\JsonApi\Contracts\Document\ElementInterface;

/**
 * @package Neomerx\JsonApi
 */
class Element implements ElementInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string|int
     */
    private $idx;

    /**
     * @var array|null
     */
    private $attributes;

    /**
     * @var string
     */
    private $selfUrl;

    /**
     * @var Iterator
     */
    private $links;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @param string            $type
     * @param string|int        $idx
     * @param array             $attributes
     * @param string            $selfUrl
     * @param Iterator          $links
     * @param array|object|null $meta
     */
    public function __construct(
        $type,
        $idx,
        array $attributes,
        $selfUrl,
        Iterator $links,
        $meta
    ) {
        assert('is_string($type) && (is_string($idx) || is_int($idx)) && (is_null($selfUrl) || is_string($selfUrl))');
        assert('(is_null($meta) || is_array($meta) || is_object($meta))');

        $this->idx         = $idx;
        $this->meta        = $meta;
        $this->type        = $type;
        $this->links       = $links;
        $this->selfUrl     = $selfUrl;
        $this->attributes  = $attributes;
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
        return (string)$this->idx;
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
    public function getLinks()
    {
        return $this->links;
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
        return $this->selfUrl !== null;
    }

    /**
     * @inheritdoc
     */
    public function isShowMeta()
    {
        return $this->meta !== null;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
