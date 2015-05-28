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
     *
     * @return array
     */
    public function convertDataResourceToArray(ResourceObjectInterface $resource)
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
    public function convertIncludedResourceToArray(ResourceObjectInterface $resource)
    {
        return $this
            ->convertResourceToArray($resource, $resource->isShowSelfInIncluded(), $resource->isShowMetaInIncluded());
    }

    /**
     * @param string        $url
     * @param LinkInterface $subLink
     *
     * @return string|array
     */
    public function concatUrls($url, LinkInterface $subLink)
    {
        $subUrl = $subLink->getSubHref();

        $urlEndsWithSlash   = (substr($url, -1) === '/');
        $subStartsWithSlash = (substr($subUrl, 0, 1) === '/');
        if ($urlEndsWithSlash === false && $subStartsWithSlash === false) {
            $resultUrl = $url . '/' . $subUrl;
        } elseif (($urlEndsWithSlash xor $subStartsWithSlash) === true) {
            $resultUrl = $url . $subUrl;
        } else {
            $resultUrl = rtrim($url, '/') . $subUrl;
        }

        return $this->getUrlRepresentation($resultUrl, $subLink->getMeta());
    }

    /**
     * @param LinkInterface[]|null $links
     *
     * @return string|null|array
     */
    public function getLinksRepresentation($links)
    {
        $result = null;
        if ($links !== null) {
            foreach ($links as $name => $link) {
                /** @var LinkInterface $link */
                $result[$name] = $this->getLinkRepresentation($link);
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
        if ($resource->isShowMetaInRelationships() === true) {
            $representation[Document::KEYWORD_META] = $resource->getMeta();
        }
        return $representation;
    }

    /**
     * @param LinkInterface|null $link
     *
     * @return string|null|array
     */
    private function getLinkRepresentation(LinkInterface $link = null)
    {
        return $link === null ? null : $this->getUrlRepresentation($link->getSubHref(), $link->getMeta());
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

        $selfUrl = $parent->getSelfUrl();

        $representation = [];
        if ($relation->isShowSelf() === true) {
            $representation[Document::KEYWORD_LINKS][Document::KEYWORD_SELF] =
                $this->concatUrls($selfUrl, $relation->getSelfLink());
        }

        if ($relation->isShowRelated() === true) {
            $representation[Document::KEYWORD_LINKS][Document::KEYWORD_RELATED] =
                $this->concatUrls($selfUrl, $relation->getRelatedLink());
        }

        if ($relation->isShowData() === true) {
            $representation[Document::KEYWORD_LINKAGE_DATA][] = $this->getLinkageRepresentation($resource);
        }

        if ($relation->isShowMeta() === true) {
            $representation[Document::KEYWORD_META] = $resource->getMeta();
        }

        if ($relation->isShowPagination() === true && $relation->getPagination() !== null) {
            if (empty($representation[Document::KEYWORD_LINKS]) === true) {
                $representation[Document::KEYWORD_LINKS] =
                    $this->getLinksRepresentation($relation->getPagination());
            } else {
                $representation[Document::KEYWORD_LINKS] +=
                    $this->getLinksRepresentation($relation->getPagination());
            }
        }

        assert(
            '$relation->isShowSelf()||$relation->isShowRelated()||$relation->isShowData()||$relation->isShowMeta()',
            'Specification requires at least one of them to be shown'
        );

        return $representation;
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
    private function convertResourceToArray(ResourceObjectInterface $resource, $isShowSelf, $isShowMeta)
    {
        assert('is_bool($isShowSelf) && is_bool($isShowMeta)');

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
        if (empty($attributes) === false) {
            $representation[Document::KEYWORD_ATTRIBUTES] = $attributes;
        }

        // reserve placeholder for relationships, otherwise it would be added after
        // links and meta which is not visually beautiful
        $representation[Document::KEYWORD_RELATIONSHIPS] = null;

        if ($isShowSelf === true) {
            $representation[Document::KEYWORD_LINKS][Document::KEYWORD_SELF] = $resource->getSelfUrl();
        }

        if ($isShowMeta === true) {
            $representation[Document::KEYWORD_META] = $resource->getMeta();
        }

        return $representation;
    }
}
