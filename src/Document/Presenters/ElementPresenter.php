<?php namespace Neomerx\JsonApi\Document\Presenters;

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

use \InvalidArgumentException;
use \Neomerx\JsonApi\Document\Document;
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\I18n\Translator as T;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * This is an auxiliary class for Document that help presenting elements.
 *
 * @package Neomerx\JsonApi
 */
class ElementPresenter
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @param Document $document
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * @param array                       $target
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     * @param mixed                       $value
     *
     * @return void
     */
    public function setRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation,
        $value
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $name         = $relation->getName();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent object might be already fully parsed (with children) so
        // - it won't exist in $target
        // - it won't make any sense to parse it again (we'll got exactly the same result and it will be thrown away
        //   as duplicate relations/included resources are not allowed)
        if ($parentExists === true) {
            $isOk = isset($target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name]) === false;
            $isOk ?: Exceptions::throwLogicException();

            $representation = [];

            if ($relation->isShowData() === true) {
                $representation[Document::KEYWORD_LINKAGE_DATA] = $value;
            }

            $representation += $this->getRelationRepresentation($parent, $relation);

            $target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name] = $representation;
        }
    }

    /**
     * @param array                       $target
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     * @param ResourceObjectInterface     $resource
     *
     * @return void
     */
    public function addRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation,
        ResourceObjectInterface $resource
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent might be already added to included to it won't be in 'target' buffer
        if ($parentExists === true) {
            $parentAlias = &$target[$parentType][$parentId];

            $name = $relation->getName();
            $alreadyGotRelation = isset($parentAlias[Document::KEYWORD_RELATIONSHIPS][$name]);

            $linkage = null;
            if ($relation->isShowData() === true) {
                $linkage = $this->getLinkageRepresentation($resource);
            }

            if ($alreadyGotRelation === false) {
                // ... add the first linkage
                $representation = [];
                if ($linkage !== null) {
                    if ($resource->isInArray() === true) {
                        // original data in array
                        $representation[Document::KEYWORD_LINKAGE_DATA][] = $linkage;
                    } else {
                        // original data not in array (just object)
                        $representation[Document::KEYWORD_LINKAGE_DATA] = $linkage;
                    }
                }
                $representation += $this->getRelationRepresentation($parent, $relation);

                $parentAlias[Document::KEYWORD_RELATIONSHIPS][$name] = $representation;
            } elseif ($alreadyGotRelation === true && $linkage !== null) {
                // Check data in '$name' relationship are marked as not arrayed otherwise
                // it's fail to add multiple data instances
                $resource->isInArray() === true ?: Exceptions::throwLogicException();

                // ... or add another linkage
                $parentAlias[Document::KEYWORD_RELATIONSHIPS][$name][Document::KEYWORD_LINKAGE_DATA][] = $linkage;
            }
        }
    }

    /**
     * Convert resource object for 'data' section to array.
     *
     * @param ResourceObjectInterface $resource
     * @param bool                    $isShowAttributes
     *
     * @return array
     */
    public function convertDataResourceToArray(ResourceObjectInterface $resource, $isShowAttributes)
    {
        return $this->convertResourceToArray(
            $resource,
            $resource->getResourceLinks(),
            $resource->getPrimaryMeta(),
            $isShowAttributes
        );
    }

    /**
     * Convert resource object for 'included' section to array.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    public function convertIncludedResourceToArray(ResourceObjectInterface $resource)
    {
        return $this->convertResourceToArray(
            $resource,
            $resource->getIncludedResourceLinks(),
            $resource->getInclusionMeta(),
            $resource->isShowAttributesInIncluded()
        );
    }

    /**
     * @param string|null                                                        $prefix
     * @param array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>|null $links
     *
     * @return array|null|string
     */
    public function getLinksRepresentation($prefix = null, $links = null)
    {
        $result = null;
        if (empty($links) === false) {
            foreach ($links as $name => $link) {
                /** @var LinkInterface $link */
                $result[$name] = $this->getLinkRepresentation($prefix, $link);
            }
        }

        return $result;
    }

    /**
     * @param string            $url
     * @param null|object|array $meta
     *
     * @return string|array
     */
    private function getUrlRepresentation($url, $meta = null)
    {
        if ($meta === null) {
            return $url;
        } else {
            return [
                Document::KEYWORD_HREF => $url,
                Document::KEYWORD_META => $meta,
            ];
        }
    }

    /**
     * @param ResourceObjectInterface $resource
     *
     * @return array<string,string>
     */
    private function getLinkageRepresentation(ResourceObjectInterface $resource)
    {
        $representation = [
            Document::KEYWORD_TYPE => $resource->getType(),
            Document::KEYWORD_ID   => $resource->getId(),
        ];
        if (($meta = $resource->getLinkageMeta()) !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }
        return $representation;
    }

    /**
     * @param string|null        $prefix
     * @param LinkInterface|null $link
     *
     * @return array|null|string
     */
    private function getLinkRepresentation($prefix = null, LinkInterface $link = null)
    {
        return $link === null ? null : $this->getUrlRepresentation(
            $link->isTreatAsHref() === true ? $link->getSubHref() : $prefix . $link->getSubHref(),
            $link->getMeta()
        );
    }

    /**
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     *
     * @return array
     */
    private function getRelationRepresentation(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation
    ) {
        $isOk = ($relation->getName() !== Document::KEYWORD_SELF);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                '\'%s\' is a reserved keyword and cannot be used as a relationship name in type \'%s\'',
                [Document::KEYWORD_SELF, $parent->getType()]
            ));
        }

        $representation = [];

        if (($meta = $relation->getMeta()) !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        $baseUrl = null;
        if (($selfSubLink = $parent->getSelfSubLink()) !== null) {
            $baseUrl = $selfSubLink->isTreatAsHref() === true ? $selfSubLink->getSubHref() . '/' :
                $this->document->getUrlPrefix() . $selfSubLink->getSubHref() . '/';
        }

        foreach ($relation->getLinks() as $name => $link) {
            $representation[Document::KEYWORD_LINKS][$name] = $this->getLinkRepresentation($baseUrl, $link);
        }

        return $representation;
    }

    /**
     * Convert resource object to array.
     *
     * @param ResourceObjectInterface $resource
     * @param array                   $resourceLinks
     * @param mixed                   $meta
     * @param bool                    $isShowAttributes
     *
     * @return array
     */
    private function convertResourceToArray(ResourceObjectInterface $resource, $resourceLinks, $meta, $isShowAttributes)
    {
        $representation = [
            Document::KEYWORD_TYPE => $resource->getType(),
            Document::KEYWORD_ID   => $resource->getId(),
        ];

        $attributes = $resource->getAttributes();

        // "type" and "id" are reserved keywords and cannot be used as resource object attributes
        $isOk = (isset($attributes[Document::KEYWORD_TYPE]) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                '\'%s\' is a reserved keyword and cannot be used as attribute name in type \'%s\'',
                [Document::KEYWORD_TYPE, $resource->getType()]
            ));
        }
        $isOk = (isset($attributes[Document::KEYWORD_ID]) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t(
                '\'%s\' is a reserved keyword and cannot be used as attribute name in type \'%s\'',
                [Document::KEYWORD_ID, $resource->getType()]
            ));
        }

        if ($isShowAttributes === true && empty($attributes) === false) {
            $representation[Document::KEYWORD_ATTRIBUTES] = $attributes;
        }

        // reserve placeholder for relationships, otherwise it would be added after
        // links and meta which is not visually beautiful
        $representation[Document::KEYWORD_RELATIONSHIPS] = null;

        if (empty($resourceLinks) === false) {
            foreach ($resourceLinks as $linkName => $link) {
                /** @var LinkInterface $link */
                $representation[Document::KEYWORD_LINKS][$linkName] =
                    $this->getLinkRepresentation($this->document->getUrlPrefix(), $link);
            }
        }

        if ($meta !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        return $representation;
    }
}
