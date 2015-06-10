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

use \Neomerx\JsonApi\Document\Document;
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
     * @param RelationshipObjectInterface $current
     * @param mixed                       $url
     *
     * @return void
     */
    public function setRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $current,
        $url
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $name         = $current->getName();
        $parentExists = isset($target[$parentType][$parentId]);

        assert('$parentExists === true');
        assert('isset($target[$parentType][$parentId][\''.Document::KEYWORD_RELATIONSHIPS.'\'][$name]) === false');

        if ($parentExists === true) {
            $target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name] = $url;
        }
    }

    /**
     * @param array                       $target
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     * @param ResourceObjectInterface     $resource
     *
     * @return void
     */
    public function addRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship,
        ResourceObjectInterface $resource
    ) {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent might be already added to included to it won't be in 'target' buffer
        if ($parentExists === true) {
            $name = $relationship->getName();
            $alreadyGotData = isset($target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name]);
            if ($alreadyGotData === false) {
                // ... add the first one
                $target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name] =
                    $this->getRelationRepresentation($parent, $relationship, $resource);
            } else {
                // ... or add another relation
                $target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS]
                    [$name][Document::KEYWORD_LINKAGE_DATA][] = $this->getLinkageRepresentation($resource);
            }
        }
    }

    /**
     * Correct empty or single relationships.
     *
     * @param array $resource
     *
     * @return array
     */
    public function correctRelationships(array $resource)
    {
        if (empty($resource[Document::KEYWORD_RELATIONSHIPS]) === false) {
            foreach ($resource[Document::KEYWORD_RELATIONSHIPS] as &$relation) {
                if (isset($relation[Document::KEYWORD_LINKAGE_DATA]) === true &&
                    empty($relation[Document::KEYWORD_LINKAGE_DATA]) === false &&
                    count($relation[Document::KEYWORD_LINKAGE_DATA]) === 1
                ) {
                    $tmp = $relation[Document::KEYWORD_LINKAGE_DATA][0];
                    unset($relation[Document::KEYWORD_LINKAGE_DATA][0]);
                    $relation[Document::KEYWORD_LINKAGE_DATA] = $tmp;
                }
            }
        } else {
            unset($resource[Document::KEYWORD_RELATIONSHIPS]);
        }

        return $resource;
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
            $resource->isShowSelf(),
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
            $resource->isShowSelfInIncluded(),
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
     * @param ResourceObjectInterface     $resource
     *
     * @return array
     */
    private function getRelationRepresentation(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation,
        ResourceObjectInterface $resource
    ) {
        assert(
            '$relation->getName() !== \''.Document::KEYWORD_SELF.'\'',
            '"self" is a reserved keyword and cannot be used as a related resource link name'
        );

        $representation = [];

        if ($relation->isShowData() === true) {
            $representation[Document::KEYWORD_LINKAGE_DATA][] = $this->getLinkageRepresentation($resource);
        }

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
     * @param bool                    $isShowSelf
     * @param mixed                   $meta
     * @param bool                    $isShowAttributes
     *
     * @return array
     */
    private function convertResourceToArray(ResourceObjectInterface $resource, $isShowSelf, $meta, $isShowAttributes)
    {
        $representation = [
            Document::KEYWORD_TYPE => $resource->getType(),
            Document::KEYWORD_ID   => $resource->getId(),
        ];

        $attributes = $resource->getAttributes();
        assert(
            'isset($attributes[\''.Document::KEYWORD_TYPE.'\']) === false && '.
            'isset($attributes[\''.Document::KEYWORD_ID.'\']) === false',
            '"type" and "id" are reserved keywords and cannot be used as resource object attributes'
        );
        if ($isShowAttributes === true && empty($attributes) === false) {
            $representation[Document::KEYWORD_ATTRIBUTES] = $attributes;
        }

        // reserve placeholder for relationships, otherwise it would be added after
        // links and meta which is not visually beautiful
        $representation[Document::KEYWORD_RELATIONSHIPS] = null;

        if ($isShowSelf === true) {
            $representation[Document::KEYWORD_LINKS][Document::KEYWORD_SELF] =
                $this->getLinkRepresentation($this->document->getUrlPrefix(), $resource->getSelfSubLink());
        }

        if ($meta !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        return $representation;
    }
}
