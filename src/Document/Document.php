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
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Document\ElementInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;

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
     * @var array
     */
    private $data;

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
        $this->errors          = [];
        $this->meta            = null;
        $this->links           = [];
        $this->data            = [];
        $this->included        = [];
        $this->isIncludedMarks = [];
    }

    /**
     * @inheritdoc
     */
    public function setSelfUrlToDocumentLinks($url)
    {
        assert('is_string($url)');
        $this->links[self::KEYWORD_SELF] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setFirstUrlToDocumentLinks($url)
    {
        assert('is_string($url)');
        $this->links[self::KEYWORD_FIRST] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setLastUrlToDocumentLinks($url)
    {
        assert('is_string($url)');
        $this->links[self::KEYWORD_LAST] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setPrevUrlToDocumentLinks($url)
    {
        assert('is_string($url)');
        $this->links[self::KEYWORD_PREV] = $url;
    }

    /**
     * @inheritdoc
     */
    public function setNextUrlToDocumentLinks($url)
    {
        assert('is_string($url)');
        $this->links[self::KEYWORD_NEXT] = $url;
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
    public function addToIncluded(ElementInterface $element)
    {
        $idx  = $element->getId();
        $type = $element->getType();
        if (isset($this->isIncludedMarks[$type][$idx]) === false) {
            $this->isIncludedMarks[$type][$idx] = true;
            $this->included[] = $this->getElementRepresentation($element);
        }
    }

    /**
     * @inheritdoc
     */
    public function addToData(ElementInterface $element)
    {
        $this->data !== null ?: $this->data = [];
        $this->data[] = $this->getElementRepresentation($element);
    }

    /**
     * @inheritdoc
     */
    public function setDataEmpty()
    {
        $this->data[] = [];
    }

    /**
     * @inheritdoc
     */
    public function setDataNull()
    {
        $this->data = null;
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
        $document[self::KEYWORD_DATA] = (count($this->data) === 1 ? $this->data[0] : $this->data);
        if (empty($this->included) === false) {
            $document[self::KEYWORD_INCLUDED] = $this->included;
        }

        return $document;
    }

    /**
     * @inheritdoc
     */
    protected function getElementRepresentation(ElementInterface $element)
    {
        $representation = [
            self::KEYWORD_TYPE => $element->getType(),
            self::KEYWORD_ID   => $element->getId(),
        ];

        $attributes = $element->getAttributes();
        assert(
            'isset($attributes[self::KEYWORD_TYPE]) === false && isset($attributes[self::KEYWORD_ID]) === false',
            '"type" and "id" are reserved keywords and cannot be used as resource object attributes'
        );
        if (empty($attributes) === false) {
            $representation += $attributes;
        }

        if ($element->isShowSelf() === true) {
            $representation[self::KEYWORD_LINKS][self::KEYWORD_SELF] = $element->getSelfUrl();
        }

        foreach ($element->getLinks() as $link) {
            /** @var LinkInterface $link */
            $name = $link->getName();
            assert(
                'is_string($name) === true && $name !== self::KEYWORD_SELF',
                '"self" is a reserved keyword and cannot be used as a related resource link name'
            );

            if ($link->isOnlyRelated() === true) {
                $representation[self::KEYWORD_LINKS][$name] = $link->getRelatedUrl();
                continue;
            }

            if ($link->isShowSelf() === true) {
                $representation[self::KEYWORD_LINKS][$name][self::KEYWORD_SELF] = $link->getSelfUrl();
            }

            if ($link->isShowRelated() === true) {
                $representation[self::KEYWORD_LINKS][$name][self::KEYWORD_RELATED] = $link->getRelatedUrl();
            }

            $linkageIds = $link->getLinkageIds();
            $idsCount   = count($linkageIds);
            if ($idsCount > 0) {
                if ($idsCount === 1) {
                    $representation[self::KEYWORD_LINKS][$name][self::KEYWORD_LINKAGE] = [
                        self::KEYWORD_TYPE => $link->getType(),
                        self::KEYWORD_ID   => (string)$linkageIds[0],
                    ];
                } else {
                    foreach ($linkageIds as $idx) {
                        $representation[self::KEYWORD_LINKS][$name][self::KEYWORD_LINKAGE][] = [
                            self::KEYWORD_TYPE => $link->getType(),
                            self::KEYWORD_ID   => (string)$idx,
                        ];
                    }
                }
            } else {
                // That's either null or [] link
                $representation[self::KEYWORD_LINKS][$name] = ($linkageIds === null ? null : []);
            }

            if ($link->isShowMeta() === true) {
                $representation[self::KEYWORD_LINKS][$name][self::KEYWORD_META] = $link->getMeta();
            }
        }

        if ($element->isShowMeta() === true) {
            $representation[self::KEYWORD_META] = $element->getMeta();
        }

        return $representation;
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
}
