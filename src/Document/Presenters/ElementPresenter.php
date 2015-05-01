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
use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\PaginationLinksInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;

/**
 * This is an auxiliary class for Document that help presenting elements.
 *
 * @package Neomerx\JsonApi
 */
class ElementPresenter
{
    /**
     * @param array                   $target
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     * @param mixed                   $url
     *
     * @return void
     */
    public function setLinkTo(
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
        assert('isset($target[$parentType][$parentId][\''.Document::KEYWORD_LINKS.'\'][$name]) === false');

        if ($parentExists === true) {
            $target[$parentType][$parentId][Document::KEYWORD_LINKS][$name] = $url;
        }
    }

    /**
     * @param array                   $target
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addLinkTo(
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
            $alreadyGotLinkages = isset($target[$parentType][$parentId][Document::KEYWORD_LINKS][$name]);
            if ($alreadyGotLinkages === false) {
                // ... add the first one
                $target[$parentType][$parentId][Document::KEYWORD_LINKS][$name] =
                    $this->getLinkRepresentation($parent, $link, $resource);
            } else {
                // ... or add another linkage
                $target[$parentType][$parentId][Document::KEYWORD_LINKS][$name][Document::KEYWORD_LINKAGE][] =
                    $this->getLinkageRepresentation($resource);
            }
        }
    }

    /**
     * @param DocumentLinksInterface $links
     *
     * @return array
     */
    public function getDocumentLinksRepresentation(DocumentLinksInterface $links)
    {
        $representation = array_merge([
            Document::KEYWORD_SELF  => $links->getSelfUrl(),
        ], $this->getPaginationLinksRepresentation($links));

        return array_filter($representation, function ($value) {
            return $value !== null;
        });
    }

    /**
     * @param array $resource
     *
     * @return array
     */
    public function correctSingleLinks(array $resource)
    {
        if (empty($resource[Document::KEYWORD_LINKS]) === false) {
            foreach ($resource[Document::KEYWORD_LINKS] as &$linkOrSelf) {
                if (isset($linkOrSelf[Document::KEYWORD_LINKAGE]) === true &&
                    empty($linkOrSelf[Document::KEYWORD_LINKAGE]) === false &&
                    count($linkOrSelf[Document::KEYWORD_LINKAGE]) === 1
                ) {
                    $tmp = $linkOrSelf[Document::KEYWORD_LINKAGE][0];
                    unset($linkOrSelf[Document::KEYWORD_LINKAGE]);
                    $linkOrSelf[Document::KEYWORD_LINKAGE] = $tmp;
                }
            }
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
     * @param string $url
     * @param string $subUrl
     *
     * @return string
     */
    public function concatUrls($url, $subUrl)
    {
        $urlEndsWithSlash   = (substr($url, -1) === '/');
        $subStartsWithSlash = (substr($subUrl, 0, 1) === '/');
        if ($urlEndsWithSlash === false && $subStartsWithSlash === false) {
            return $url . '/' . $subUrl;
        } elseif (($urlEndsWithSlash xor $subStartsWithSlash) === true) {
            return $url . $subUrl;
        } else {
            return rtrim($url, '/') . $subUrl;
        }
    }

    /**
     * @param ResourceObjectInterface $resource
     *
     * @return array<string,string>
     */
    private function getLinkageRepresentation(ResourceObjectInterface $resource)
    {
        return [
            Document::KEYWORD_TYPE => $resource->getType(),
            Document::KEYWORD_ID   => $resource->getId(),
        ];
    }

    /**
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    private function getLinkRepresentation(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    ) {
        assert(
            '$link->getName() !== \''.Document::KEYWORD_SELF.'\'',
            '"self" is a reserved keyword and cannot be used as a related resource link name'
        );

        $selfUrl = $parent->getSelfUrl();

        $representation = [];
        if ($link->isShowSelf() === true) {
            $representation[Document::KEYWORD_SELF] = $this->concatUrls($selfUrl, $link->getSelfSubUrl());
        }

        if ($link->isShowRelated() === true) {
            $representation[Document::KEYWORD_RELATED] = $this->concatUrls($selfUrl, $link->getRelatedSubUrl());
        }

        if ($link->isShowLinkage() === true) {
            $representation[Document::KEYWORD_LINKAGE][] = $this->getLinkageRepresentation($resource);
        }

        if ($link->isShowMeta() === true) {
            $representation[Document::KEYWORD_META] = $resource->getMeta();
        }

        if ($link->isShowPagination() === true) {
            $representation = array_merge(
                $representation,
                $this->getPaginationLinksRepresentation($link->getPagination())
            );
        }

        assert(
            '$link->isShowSelf() || $link->isShowRelated() || $link->isShowLinkage() || $link->isShowMeta()',
            'Specification requires at least one of them to be shown'
        );

        return $representation;
    }

    /**
     * @param PaginationLinksInterface $links
     *
     * @return array
     */
    private function getPaginationLinksRepresentation(PaginationLinksInterface $links)
    {
        return array_filter([
            Document::KEYWORD_FIRST => $links->getFirstUrl(),
            Document::KEYWORD_LAST  => $links->getLastUrl(),
            Document::KEYWORD_PREV  => $links->getPrevUrl(),
            Document::KEYWORD_NEXT  => $links->getNextUrl(),
        ], function ($value) {
            return $value !== null;
        });
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
            $representation += $attributes;
        }

        if ($isShowSelf === true) {
            $representation[Document::KEYWORD_LINKS][Document::KEYWORD_SELF] = $resource->getSelfUrl();
        }

        if ($isShowMeta === true) {
            $representation[Document::KEYWORD_META] = $resource->getMeta();
        }

        return $representation;
    }
}
