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

use \Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
class Link implements LinkInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isOnlyRelated;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $selfUrl;

    /**
     * @var string
     */
    private $relatedUrl;

    /**
     * @var int[]|string[]
     */
    private $linkageIds;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @param string            $name
     * @param bool              $isOnlyRelated
     * @param string            $type
     * @param int[]|string[]    $linkageIds
     * @param string|null       $selfUrl
     * @param string|null       $relatedUrl
     * @param array|object|null $meta
     */
    public function __construct(
        $name,
        $isOnlyRelated,
        $type,
        array $linkageIds,
        $selfUrl,
        $relatedUrl,
        $meta
    ) {
        assert('is_string($name)');
        assert('is_bool($isOnlyRelated)');
        assert('$isOnlyRelated === true || (is_string($type) && empty($linkageIds) === false)');
        assert('is_string($selfUrl) || is_null($selfUrl)');
        assert('is_string($relatedUrl) || is_null($relatedUrl)');
        assert('is_null($meta) || is_array($meta) || is_object($meta)');

        $this->name          = $name;
        $this->meta          = $meta;
        $this->type          = $type;
        $this->selfUrl       = $selfUrl;
        $this->relatedUrl    = $relatedUrl;
        $this->linkageIds    = $linkageIds;
        $this->isOnlyRelated = $isOnlyRelated;
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
    public function getType()
    {
        return $this->type;
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
    public function getRelatedUrl()
    {
        return $this->relatedUrl;
    }

    /**
     * @inheritdoc
     */
    public function isShowRelated()
    {
        return $this->relatedUrl !== null;
    }

    /**
     * @inheritdoc
     */
    public function getLinkageIds()
    {
        return $this->linkageIds;
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

    /**
     * @inheritdoc
     */
    public function isOnlyRelated()
    {
        return $this->isOnlyRelated;
    }
}
