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
use \Neomerx\JsonApi\Document\Presenters\ElementPresenter;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
class Document implements DocumentInterface
{
    /** Reserved keyword */
    const KEYWORD_LINKS         = 'links';
    /** Reserved keyword */
    const KEYWORD_HREF          = 'href';
    /** Reserved keyword */
    const KEYWORD_RELATIONSHIPS = 'relationships';
    /** Reserved keyword */
    const KEYWORD_SELF          = 'self';
    /** Reserved keyword */
    const KEYWORD_FIRST         = 'first';
    /** Reserved keyword */
    const KEYWORD_LAST          = 'last';
    /** Reserved keyword */
    const KEYWORD_NEXT          = 'next';
    /** Reserved keyword */
    const KEYWORD_PREV          = 'prev';
    /** Reserved keyword */
    const KEYWORD_RELATED       = 'related';
    /** Reserved keyword */
    const KEYWORD_LINKAGE_DATA  = self::KEYWORD_DATA;
    /** Reserved keyword */
    const KEYWORD_TYPE          = 'type';
    /** Reserved keyword */
    const KEYWORD_ID            = 'id';
    /** Reserved keyword */
    const KEYWORD_ATTRIBUTES    = 'attributes';
    /** Reserved keyword */
    const KEYWORD_META          = 'meta';
    /** Reserved keyword */
    const KEYWORD_DATA          = 'data';
    /** Reserved keyword */
    const KEYWORD_INCLUDED      = 'included';

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
    const KEYWORD_ERRORS_META   = 'meta';
    /** Reserved keyword */
    const KEYWORD_ERRORS_SOURCE = 'source';

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
     * @var ElementPresenter
     */
    private $presenter;

    /**
     * @var bool
     */
    private $showData = true;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->presenter = new ElementPresenter();
    }

    /**
     * @inheritdoc
     */
    public function setDocumentLinks(DocumentLinksInterface $links)
    {
        $this->links = $this->presenter->getDocumentLinksRepresentation($links);
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
            $this->bufferForIncluded[$type][$idx] = $this->presenter->convertIncludedResourceToArray($resource);
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
        $this->bufferForData[$type][$idx] = $this->presenter->convertDataResourceToArray($resource);
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
    public function addRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship,
        ResourceObjectInterface $resource
    ) {
        $this->presenter->addRelationshipTo($this->bufferForData, $parent, $relationship, $resource);
    }

    /**
     * @inheritdoc
     */
    public function addRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship,
        ResourceObjectInterface $resource
    ) {
        $this->presenter->addRelationshipTo($this->bufferForIncluded, $parent, $relationship, $resource);
    }

    /**
     * @inheritdoc
     */
    public function addReferenceToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current)
    {
        $url = $this->presenter->concatUrls($parent->getSelfUrl(), $current->getRelatedLink());
        $this->presenter->setRelationshipTo($this->bufferForData, $parent, $current, $url);
    }

    /**
     * @inheritdoc
     */
    public function addReferenceToIncluded(ResourceObjectInterface $parent, RelationshipObjectInterface $current)
    {
        $url = $this->presenter->concatUrls($parent->getSelfUrl(), $current->getRelatedLink());
        $this->presenter->setRelationshipTo($this->bufferForIncluded, $parent, $current, $url);
    }

    /**
     * @inheritdoc
     */
    public function addEmptyRelationshipToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current)
    {
        $this->presenter->setRelationshipTo($this->bufferForData, $parent, $current, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullRelationshipToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current)
    {
        $this->presenter->setRelationshipTo($this->bufferForData, $parent, $current, null);
    }

    /**
     * @inheritdoc
     */
    public function addEmptyRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $current
    ) {
        $this->presenter->setRelationshipTo($this->bufferForIncluded, $parent, $current, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullRelationshipToIncluded(ResourceObjectInterface $parent, RelationshipObjectInterface $current)
    {
        $this->presenter->setRelationshipTo($this->bufferForIncluded, $parent, $current, null);
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
            $this->data[] = $this->presenter->correctRelationships($this->bufferForData[$type][$idx]);
            unset($this->bufferForData[$type][$idx]);
        }

        if ($foundInIncluded === true) {
            $this->included[] = $this->presenter->correctRelationships($this->bufferForIncluded[$type][$idx]);
            unset($this->bufferForIncluded[$type][$idx]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getDocument()
    {
        if ($this->errors !== null) {
            return [self::KEYWORD_ERRORS => $this->errors];
        }

        $document = array_filter([
            self::KEYWORD_META     => $this->meta,
            self::KEYWORD_LINKS    => $this->links,
            self::KEYWORD_DATA     => true, // this field wont be filtered
            self::KEYWORD_INCLUDED => $this->included,
        ], function ($value) {
            return $value !== null;
        });

        if ($this->showData === true) {
            $isDataNotArray               = ($this->isDataArrayed === false && empty($this->data) === false);
            $document[self::KEYWORD_DATA] = ($isDataNotArray ? $this->data[0] : $this->data);
        } else {
            unset($document[self::KEYWORD_DATA]);
        }

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function unsetData()
    {
        $this->showData = false;
    }

    /**
     * @inheritdoc
     */
    public function addError(ErrorInterface $error)
    {
        $errorId = (($errorId = $error->getId()) === null ? null : (string)$errorId);

        $representation = array_filter([
            self::KEYWORD_ERRORS_ID     => $errorId,
            self::KEYWORD_ERRORS_HREF   => $error->getHref(),
            self::KEYWORD_ERRORS_STATUS => $error->getStatus(),
            self::KEYWORD_ERRORS_CODE   => $error->getCode(),
            self::KEYWORD_ERRORS_TITLE  => $error->getTitle(),
            self::KEYWORD_ERRORS_DETAIL => $error->getDetail(),
            self::KEYWORD_ERRORS_SOURCE => $error->getSource(),
            self::KEYWORD_ERRORS_META   => $error->getMeta(),
        ], function ($value) {
            return $value !== null;
        });

        $this->errors[] = $representation;
    }
}
