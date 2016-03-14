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

use \Closure;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Document\Presenters\ElementPresenter;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
class Document implements DocumentInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var array
     */
    private $errors;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @var array|null|string
     */
    private $links;

    /**
     * @var array
     */
    private $hasBeenMetAlready;

    /**
     * @var array|null
     */
    private $included;

    /**
     * @var int
     */
    private $includedIndex = 0;

    /**
     * @var array
     */
    private $includedResources = [];

    /**
     * @var array|null
     */
    private $version;

    /**
     * @var array|null
     */
    private $data;

    /**
     * @var array
     */
    private $bufferForData = [];

    /**
     * @var array
     */
    private $bufferForIncluded = [];

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
     * @var string|null
     */
    private $urlPrefix;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->presenter = new ElementPresenter($this);
    }

    /**
     * @inheritdoc
     */
    public function setDocumentLinks($links)
    {
        $this->links = $this->presenter->getLinksRepresentation($this->urlPrefix, $links);
    }

    /**
     * @inheritdoc
     */
    public function setMetaToDocument($meta)
    {
        (is_object($meta) === true || is_array($meta) === true) ?: Exceptions::throwInvalidArgument('meta', $meta);
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function addToIncluded(ResourceObjectInterface $resource)
    {
        $idx  = $resource->getId();
        $type = $resource->getType();
        if (isset($this->hasBeenMetAlready[$type][$idx]) === false) {
            $this->bufferForIncluded[$type][$idx] = $this->presenter->convertIncludedResourceToArray($resource);
            $this->hasBeenMetAlready[$type][$idx] = true;
        }
    }

    /**
     * @inheritdoc
     */
    public function addToData(ResourceObjectInterface $resource)
    {
        // check if 'not-arrayed' data were added you cannot add to 'non-array' data section anymore
        ($this->isDataArrayed === true || $this->isDataArrayed === null) ?: Exceptions::throwLogicException();

        $this->isDataArrayed !== null ?: $this->isDataArrayed = $resource->isInArray();

        // check all resources have the same isInArray flag
        ($this->isDataArrayed === $resource->isInArray()) ?: Exceptions::throwLogicException();

        $idx  = $resource->getId();
        $type = $resource->getType();

        isset($this->bufferForData[$type][$idx]) === false ?: Exceptions::throwLogicException();

        $this->bufferForData[$type][$idx] = $this->presenter->convertDataResourceToArray($resource, true);
        $this->hasBeenMetAlready[$type][$idx] = true;

        // check if resource has already been added to included
        // (for example as related resource of one of the previous main resources)
        if (isset($this->includedResources[$type][$idx]) === true) {
            $includedIndex = $this->includedResources[$type][$idx];

            // remove duplicate from 'included' (leave only in main resources)
            unset($this->included[$includedIndex]);
        }
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
    public function addEmptyRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    ) {
        $this->presenter->setRelationshipTo($this->bufferForData, $parent, $relationship, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    ) {
        $this->presenter->setRelationshipTo($this->bufferForData, $parent, $relationship, null);
    }

    /**
     * @inheritdoc
     */
    public function addEmptyRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    ) {
        $this->presenter->setRelationshipTo($this->bufferForIncluded, $parent, $relationship, []);
    }

    /**
     * @inheritdoc
     */
    public function addNullRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    ) {
        $this->presenter->setRelationshipTo($this->bufferForIncluded, $parent, $relationship, null);
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

        $addMeta = function (array $representation, Closure $getMetaClosure) {
            if (empty($representation[self::KEYWORD_RELATIONSHIPS]) === true) {
                // if no relationships have been added remove empty placeholder
                unset($representation[self::KEYWORD_RELATIONSHIPS]);
            } else {
                // relationship might have meta
                $relShipsMeta = $getMetaClosure();
                if (empty($relShipsMeta) === false) {
                    $representation[self::KEYWORD_RELATIONSHIPS][self::KEYWORD_META] = $relShipsMeta;
                }
            }

            return $representation;
        };

        if ($foundInData === true) {
            $representation = $this->bufferForData[$type][$idx];
            unset($this->bufferForData[$type][$idx]);

            $this->data[] = $addMeta($representation, function () use ($resource) {
                return $resource->getRelationshipsPrimaryMeta();
            });
        }

        if ($foundInIncluded === true) {
            $representation = $this->bufferForIncluded[$type][$idx];
            unset($this->bufferForIncluded[$type][$idx]);

            $this->included[] = $addMeta($representation, function () use ($resource) {
                return $resource->getRelationshipsInclusionMeta();
            });
            // remember we added (type, id) at index
            $this->includedResources[$type][$idx] = $this->includedIndex;
            $this->includedIndex++;
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
            self::KEYWORD_JSON_API => $this->version,
            self::KEYWORD_META     => $this->meta,
            self::KEYWORD_LINKS    => $this->links,
            self::KEYWORD_DATA     => true, // this field wont be filtered
            self::KEYWORD_INCLUDED => empty($this->included) === true ? null : array_values($this->included),
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
    public function addJsonApiVersion($version, $meta = null)
    {
        $this->version = $meta === null ?
            [self::KEYWORD_VERSION => $version] : [self::KEYWORD_VERSION => $version, self::KEYWORD_META => $meta];
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
            self::KEYWORD_ERRORS_LINKS  => $this->presenter
                ->getLinksRepresentation($this->urlPrefix, $error->getLinks()),
            self::KEYWORD_ERRORS_STATUS => $error->getStatus(),
            self::KEYWORD_ERRORS_CODE   => $error->getCode(),
            self::KEYWORD_ERRORS_TITLE  => $error->getTitle(),
            self::KEYWORD_ERRORS_DETAIL => $error->getDetail(),
            self::KEYWORD_ERRORS_SOURCE => $error->getSource(),
            self::KEYWORD_ERRORS_META   => $error->getMeta(),
        ], function ($value) {
            return $value !== null;
        });

        $this->errors[] = (object)$representation;
    }

    /**
     * @inheritdoc
     */
    public function setUrlPrefix($prefix)
    {
        $this->urlPrefix = (string)$prefix;
    }

    /**
     * Get URL prefix.
     *
     * @return null|string
     */
    public function getUrlPrefix()
    {
        return $this->urlPrefix;
    }
}
