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

use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\PaginationLinksInterface;
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
    private $isShowData;

    /**
     * @var LinkInterface
     */
    private $selfLink;

    /**
     * @var LinkInterface
     */
    private $relatedLink;

    /**
     * @var bool
     */
    private $isShowMeta;

    /**
     * @var bool
     */
    private $isShowPagination;

    /**
     * @var PaginationLinksInterface|null
     */
    private $pagination;

    /**
     * @param string                        $name
     * @param object|array|null             $data
     * @param LinkInterface                 $selfLink
     * @param LinkInterface                 $relatedLink
     * @param bool                          $isShowAsRef
     * @param bool                          $isShowSelf
     * @param bool                          $isShowRelated
     * @param bool                          $isShowData
     * @param bool                          $isShowMeta
     * @param bool                          $isShowPagination
     * @param PaginationLinksInterface|null $pagination
     */
    public function __construct(
        $name,
        $data,
        LinkInterface $selfLink,
        LinkInterface $relatedLink,
        $isShowAsRef,
        $isShowSelf,
        $isShowRelated,
        $isShowData,
        $isShowMeta,
        $isShowPagination,
        $pagination
    ) {
        assert(
            'is_string($name) &&'.
            '(is_object($data) || is_array($data) || is_null($data)) &&'.
            'is_bool($isShowAsRef) && is_bool($isShowSelf) && is_bool($isShowRelated) && is_bool($isShowMeta) &&'.
            'is_bool($isShowPagination) &&'.
            '(is_null($pagination) || $pagination instanceof ' . PaginationLinksInterface::class . ')'
        );
        assert(
            '$isShowSelf || $isShowRelated || $isShowData || $isShowMeta',
            'Specification requires at least one of them to be shown'
        );

        $this->name              = $name;
        $this->data              = $data;
        $this->selfLink          = $selfLink;
        $this->relatedLink       = $relatedLink;
        $this->isShowAsReference = $isShowAsRef;
        $this->isShowSelf        = $isShowSelf;
        $this->isShowRelated     = $isShowRelated;
        $this->isShowData        = $isShowData;
        $this->isShowMeta        = $isShowMeta;
        $this->isShowPagination  = $isShowPagination;
        $this->pagination        = $pagination;
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
    public function getSelfLink()
    {
        return $this->selfLink;
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
    public function isShowData()
    {
        return $this->isShowData;
    }

    /**
     * @inheritdoc
     */
    public function getRelatedLink()
    {
        return $this->relatedLink;
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
    public function isShowPagination()
    {
        return $this->isShowPagination;
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
    public function getPagination()
    {
        return $this->pagination;
    }
}
