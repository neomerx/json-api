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

use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
class LinkObject implements LinkObjectInterface
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
     * @var bool
     */
    private $isShowAsReference;

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
    private $isShouldBeIncluded;

    /**
     * @var string|null
     */
    private $selfSubUrl;

    /**
     * @var string|null
     */
    private $relatedSubUrl;

    /**
     * @var bool
     */
    private $isShowMeta;

    /**
     * @var mixed
     */
    private $selfControllerData;

    /**
     * @var mixed
     */
    private $relatedControllerData;

    /**
     * @param string            $name
     * @param object|array|null $data
     * @param string|null       $selfSubUrl
     * @param string|null       $relatedSubUrl
     * @param bool              $isShowAsRef
     * @param bool              $isShowSelf
     * @param bool              $isShowRelated
     * @param bool              $isShowMeta
     * @param bool              $isIncluded
     * @param mixed             $selfControllerData
     * @param mixed             $relatedControllerData
     */
    public function __construct(
        $name,
        $data,
        $selfSubUrl,
        $relatedSubUrl,
        $isShowAsRef,
        $isShowSelf,
        $isShowRelated,
        $isShowMeta,
        $isIncluded,
        $selfControllerData,
        $relatedControllerData
    ) {
        assert('is_string($name)');
        assert('is_object($data) || is_array($data) || is_null($data)');
        assert('is_null($selfSubUrl) || is_string($selfSubUrl)');
        assert('is_null($relatedSubUrl) || is_string($relatedSubUrl)');
        assert('is_bool($isShowAsRef) && is_bool($isShowSelf) && is_bool($isShowRelated) && is_bool($isShowMeta)');
        assert('is_bool($isIncluded)');

        $this->name                  = $name;
        $this->data                  = $data;
        $this->selfSubUrl            = $selfSubUrl;
        $this->relatedSubUrl         = $relatedSubUrl;
        $this->isShowAsReference     = $isShowAsRef;
        $this->isShowSelf            = $isShowSelf;
        $this->isShowRelated         = $isShowRelated;
        $this->isShowMeta            = $isShowMeta;
        $this->isShouldBeIncluded    = $isIncluded;
        $this->selfControllerData    = $selfControllerData;
        $this->relatedControllerData = $relatedControllerData;
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
    public function isShowSelf()
    {
        return $this->isShowSelf;
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl()
    {
        return $this->selfSubUrl;
    }

    /**
     * @inheritdoc
     */
    public function isShowAsReference()
    {
        return $this->isShowAsReference;
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
    public function getRelatedSubUrl()
    {
        return $this->relatedSubUrl;
    }

    /**
     * @inheritdoc
     */
    public function isShouldBeIncluded()
    {
        return $this->isShouldBeIncluded;
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
    public function getLinkedData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function getSelfControllerData()
    {
        return $this->selfControllerData;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedControllerData()
    {
        return $this->relatedControllerData;
    }
}
