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

use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;

/**
 * @package Neomerx\JsonApi
 */
class Document implements DocumentInterface
{
    /** Reserved keyword */
    const KEYWORD_LINKS     = 'links';
    /** Reserved keyword */
    const KEYWORD_SELF      = 'self';
    /** Reserved keyword */
    const KEYWORD_FIRST     = 'first';
    /** Reserved keyword */
    const KEYWORD_LAST      = 'last';
    /** Reserved keyword */
    const KEYWORD_NEXT      = 'next';
    /** Reserved keyword */
    const KEYWORD_PREV      = 'prev';
    /** Reserved keyword */
    const KEYWORD_RELATED   = 'related';
    /** Reserved keyword */
    const KEYWORD_LINKAGE   = 'linkage';
    /** Reserved keyword */
    const KEYWORD_TYPE      = 'type';
    /** Reserved keyword */
    const KEYWORD_ID        = 'id';
    /** Reserved keyword */
    const KEYWORD_META      = 'meta';
    /** Reserved keyword */
    const KEYWORD_DATA      = 'data';
    /** Reserved keyword */
    const KEYWORD_INCLUDED  = 'included';

    /** Reserved keyword */
    const KEYWORD_ERRORS        = 'errors';
    /** Reserved keyword */
    const KEYWORD_ERRORS_ID     = 'id';
    /** Reserved keyword */
    const KEYWORD_ERRORS_HREF   = 'href';
    /** Reserved keyword */
    const KEYWORD_ERRORS_STATUS = 'status';
    /** Reserved keyword */
    const KEYWORD_ERRORS_CODE   = 'code';
    /** Reserved keyword */
    const KEYWORD_ERRORS_TITLE  = 'title';
    /** Reserved keyword */
    const KEYWORD_ERRORS_DETAIL = 'detail';
    /** Reserved keyword */
    const KEYWORD_ERRORS_LINKS  = 'links';
    /** Reserved keyword */
    const KEYWORD_ERRORS_PATHS  = 'paths';

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @var array
     */
    private $links;

    /**
     * @var array
     */
    private $isIncludedMarks;

    /**
     * @var array
     */
    private $included;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @var array
     */
    private $bufferForData;

    /**
     * @var array
     */
    private $bufferForIncluded;

    /**
     * If original data were in array.
     *
     * @var bool|null
     */
    private $isDataArrayed;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->resetDocument();
    }

    /**
     * @inheritdoc
     */
    private function resetDocument()
    {
        $this->errors            = [];
        $this->meta              = null;
        $this->links             = [];
        $this->data              = null;
        $this->included          = [];
        $this->isIncludedMarks   = [];
        $this->bufferForData     = [];
        $this->bufferForIncluded = [];
        $this->isDataArrayed     = null;
    }

    /**
     * @inheritdoc
     */
    public function setDocumentLinks(DocumentLinksInterface $links)
    {
        $links->getSelfUrl()  === null ?: $this->links[self::KEYWORD_SELF]  = $links->getSelfUrl();
        $links->getFirstUrl() === null ?: $this->links[self::KEYWORD_FIRST] = $links->getFirstUrl();
        $links->getLastUrl()  === null ?: $this->links[self::KEYWORD_LAST]  = $links->getLastUrl();
        $links->getPrevUrl()  === null ?: $this->links[self::KEYWORD_PREV]  = $links->getPrevUrl();
        $links->getNextUrl()  === null ?: $this->links[self::KEYWORD_NEXT]  = $links->getNextUrl();
    }

    /**
     * @inheritdoc
     */
    public function setMetaToDocument($meta)
    {
        assert('is_object($meta) || is_array($meta)');
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function addToIncluded(ResourceObjectInterface $resource)
    {
        $idx  = $resource->getId();
        $type = $resource->getType();
        if (isset($this->isIncludedMarks[$type][$idx]) === false) {
            $this->isIncludedMarks[$type][$idx] = true;
            $this->bufferForIncluded[$type][$idx] = $this->convertIncludedResourceToArray($resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function addToData(ResourceObjectInterface $resource)
    {
        // check if 'not-arrayed' data were added you cannot add to 'non-array' data section anymore
        assert('$this->isDataArrayed === null || $this->isDataArrayed === true');

        $this->isDataArrayed !== null ?: $this->isDataArrayed = $resource->isInArray();

        // check all resources have the same isInArray flag
        assert('$this->isDataArrayed === $resource->isInArray()');

        $idx  = $resource->getId();
        $type = $resource->getType();
        assert('isset($this->bufferForData[$type][$idx]) === false');
        $this->bufferForData[$type][$idx] = $this->convertDataResourceToArray($resource);
    }

    /**
     * @inheritdoc
     */
    public function setEmptyData()
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    public function setNullData()
    {
        $this->data = null;
    }

    /**
     * @inheritdoc
     */
    public function addLinkToData(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    ) {
        $this->addLinkToImpl($this->bufferForData, $parent, $link, $resource);
    }

    /**
     * @inheritdoc
     */
    public function addLinkToIncluded(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    ) {
        $this->addLinkToImpl($this->bufferForIncluded, $parent, $link, $resource);
    }

    /**
     * @param array                   $target
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    protected function addLinkToImpl(
        array &$target,
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent might be already added to included to it won't be in 'target' buffer
        if ($parentExists === true) {
            $name = $link->getName();
            $someLinkagesAlreadyAdded = isset($target[$parentType][$parentId][self::KEYWORD_LINKS][$name]);
            if ($someLinkagesAlreadyAdded === false) {
                // ... add the first one
                $target[$parentType][$parentId][self::KEYWORD_LINKS][$name] =
                    $this->getLinkRepresentation($parent, $link, $resource);
            } else {
                // ... or add another linkage
                $target[$parentType][$parentId][self::KEYWORD_LINKS][$name][self::KEYWORD_LINKAGE][] =
                    $this->getLinkageRepresentation($resource);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addReferenceToData(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $url = $this->concatUrls($parent->getSelfUrl(), $current->getRelatedSubUrl());
        $this->setLinkToImpl($this->bufferForData, $parent, $current, $url);
    }

    /**
     * @inheritdoc
     */
    public function addReferenceToIncluded(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $url = $this->concatUrls($parent->getSelfUrl(), $current->getRelatedSubUrl());
        $this->setLinkToImpl($this->bufferForIncluded, $parent, $current, $url);
    }

    /**
     * @inheritdoc
     */
    public function addEmptyLinkToData(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $this->setLinkToImpl($this->bufferForData, $parent, $current, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullLinkToData(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $this->setLinkToImpl($this->bufferForData, $parent, $current, null);
    }

    /**
     * @inheritdoc
     */
    public function addEmptyLinkToIncluded(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $this->setLinkToImpl($this->bufferForIncluded, $parent, $current, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullLinkToIncluded(ResourceObjectInterface $parent, LinkObjectInterface $current)
    {
        $this->setLinkToImpl($this->bufferForIncluded, $parent, $current, null);
    }

    /**
     * @param array                   $target
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     * @param mixed                   $url
     *
     * @return void
     */
    protected function setLinkToImpl(
        array &$target,
        ResourceObjectInterface $parent,
        LinkObjectInterface $current,
        $url
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $name         = $current->getName();
        $parentExists = isset($target[$parentType][$parentId]);

        assert('$parentExists === true');
        assert('isset($target[$parentType][$parentId][self::KEYWORD_LINKS][$name]) === false');

        if ($parentExists === true) {
            $target[$parentType][$parentId][self::KEYWORD_LINKS][$name] = $url;
        }
    }

    /**
     * @inheritdoc
     */
    public function setResourceCompleted(ResourceObjectInterface $resource)
    {
        $idx  = $resource->getId();
        $type = $resource->getType();

        $foundInData     = isset($this->bufferForData[$type][$idx]);
        $foundInIncluded = isset($this->bufferForIncluded[$type][$idx]);

        if ($foundInData === true) {
            $this->data[] = $this->correctSingleLinks($this->bufferForData[$type][$idx]);
            unset($this->bufferForData[$type][$idx]);
        }

        if ($foundInIncluded === true) {
            $this->included[] = $this->correctSingleLinks($this->bufferForIncluded[$type][$idx]);
            unset($this->bufferForIncluded[$type][$idx]);
        }
    }

    /**
     * @param array $resource
     *
     * @return array
     */
    protected function correctSingleLinks(array $resource)
    {
        if (empty($resource[self::KEYWORD_LINKS]) === false) {
            foreach ($resource[self::KEYWORD_LINKS] as &$linkOrSelf) {
                if (isset($linkOrSelf[self::KEYWORD_LINKAGE]) === true &&
                    empty($linkOrSelf[self::KEYWORD_LINKAGE]) === false &&
                    count($linkOrSelf[self::KEYWORD_LINKAGE]) === 1
                ) {
                    $tmp = $linkOrSelf[self::KEYWORD_LINKAGE][0];
                    unset($linkOrSelf[self::KEYWORD_LINKAGE]);
                    $linkOrSelf[self::KEYWORD_LINKAGE] = $tmp;
                }
            }
        }

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function getDocument()
    {
        if (empty($this->errors) === false) {
            return [self::KEYWORD_ERRORS => $this->errors];
        }

        $document = [];

        if ($this->meta !== null) {
            $document[self::KEYWORD_META] = $this->meta;
        }

        if (empty($this->links) === false) {
            $document[self::KEYWORD_LINKS] = $this->links;
        }

        $isDataNotArray = ($this->isDataArrayed === false && empty($this->data) === false);
        $document[self::KEYWORD_DATA] = ($isDataNotArray ? $this->data[0] : $this->data);

        if (empty($this->included) === false) {
            $document[self::KEYWORD_INCLUDED] = $this->included;
        }

        return $document;
    }

    /**
     * Convert resource object for 'data' section to array.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    protected function convertDataResourceToArray(ResourceObjectInterface $resource)
    {
        return $this->convertResourceToArray($resource, $resource->isShowSelf(), $resource->isShowMeta());
    }

    /**
     * Convert resource object for 'included' section to array.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    protected function convertIncludedResourceToArray(ResourceObjectInterface $resource)
    {
        return $this
            ->convertResourceToArray($resource, $resource->isShowSelfInIncluded(), $resource->isShowMetaInIncluded());
    }

    /**
     * Convert resource object to array.
     *
     * @param ResourceObjectInterface $resource
     * @param bool                    $isShowSelf
     * @param bool                    $isShowMeta
     *
     * @return array
     */
    protected function convertResourceToArray(ResourceObjectInterface $resource, $isShowSelf, $isShowMeta)
    {
        assert('is_bool($isShowSelf) && is_bool($isShowMeta)');

        $representation = [
            self::KEYWORD_TYPE => $resource->getType(),
            self::KEYWORD_ID   => $resource->getId(),
        ];

        $attributes = $resource->getAttributes();
        assert(
            'isset($attributes[self::KEYWORD_TYPE]) === false && isset($attributes[self::KEYWORD_ID]) === false',
            '"type" and "id" are reserved keywords and cannot be used as resource object attributes'
        );
        if (empty($attributes) === false) {
            $representation += $attributes;
        }

        if ($isShowSelf === true) {
            $representation[self::KEYWORD_LINKS][self::KEYWORD_SELF] = $resource->getSelfUrl();
        }

        if ($isShowMeta === true) {
            $representation[self::KEYWORD_META] = $resource->getMeta();
        }

        return $representation;
    }

    /**
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    protected function getLinkRepresentation(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    ) {
        assert(
            '$link->getName() !== self::KEYWORD_SELF',
            '"self" is a reserved keyword and cannot be used as a related resource link name'
        );

        $selfUrl = $parent->getSelfUrl();

        $representation = [];
        if ($link->isShowSelf() === true) {
            $representation[self::KEYWORD_SELF] = $this->concatUrls($selfUrl, $link->getSelfSubUrl());
        }

        if ($link->isShowRelated() === true) {
            $representation[self::KEYWORD_RELATED] = $this->concatUrls($selfUrl, $link->getRelatedSubUrl());
        }

        if ($link->isShowLinkage() === true) {
            $representation[self::KEYWORD_LINKAGE][] = $this->getLinkageRepresentation($resource);
        }

        if ($link->isShowMeta() === true) {
            $representation[self::KEYWORD_META] = $resource->getMeta();
        }

        assert(
            '$link->isShowSelf() || $link->isShowRelated() || $link->isShowLinkage() || $link->isShowMeta()',
            'Specification requires at least one of them to be shown'
        );

        return $representation;
    }

    /**
     * @param ResourceObjectInterface $resource
     *
     * @return array<string,string>
     */
    protected function getLinkageRepresentation(ResourceObjectInterface $resource)
    {
        return [
            self::KEYWORD_TYPE => $resource->getType(),
            self::KEYWORD_ID   => $resource->getId(),
        ];
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addError(ErrorInterface $error)
    {
        $representation = [];

        $error->getId()     === null ?: $representation[self::KEYWORD_ERRORS_ID]     = (string)$error->getId();
        $error->getHref()   === null ?: $representation[self::KEYWORD_ERRORS_HREF]   = $error->getHref();
        $error->getStatus() === null ?: $representation[self::KEYWORD_ERRORS_STATUS] = $error->getStatus();
        $error->getCode()   === null ?: $representation[self::KEYWORD_ERRORS_CODE]   = $error->getCode();
        $error->getTitle()  === null ?: $representation[self::KEYWORD_ERRORS_TITLE]  = $error->getTitle();
        $error->getDetail() === null ?: $representation[self::KEYWORD_ERRORS_DETAIL] = $error->getDetail();
        $error->getLinks()  === null ?: $representation[self::KEYWORD_ERRORS_LINKS]  = $error->getLinks();
        $error->getPaths()  === null ?: $representation[self::KEYWORD_ERRORS_PATHS]  = $error->getPaths();

        $members = $error->getAdditionalMembers();
        empty($members) === true ?: $representation += $members;

        $this->errors[] = $representation;
    }

    /**
     * @param string $url
     * @param string $subUrl
     *
     * @return string
     */
    private function concatUrls($url, $subUrl)
    {
        $urlEndsWithSlash      = (substr($url, -1) === '/');
        $subUrlStartsWithSlash = (substr($subUrl, 0, 1) === '/');
        if ($urlEndsWithSlash === false && $subUrlStartsWithSlash === false) {
            return $url . '/' . $subUrl;
        } elseif (($urlEndsWithSlash xor $subUrlStartsWithSlash) === true) {
            return $url . $subUrl;
        } else {
            return rtrim($url, '/') . $subUrl;
        }
    }
}
