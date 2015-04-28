<?php namespace Neomerx\JsonApi\Contracts\Document;

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
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
interface DocumentInterface
{
    /**
     * Set URLs to top-level 'links' section.
     *
     * @param DocumentLinksInterface $links
     *
     * @return void
     */
    public function setDocumentLinks(DocumentLinksInterface $links);

    /**
     * Set arbitrary meta-information about primary data to top-level 'meta' section.
     *
     * @param object|array $meta
     *
     * @return void
     */
    public function setMetaToDocument($meta);

    /**
     * Add resource to 'data' section.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addToData(ResourceObjectInterface $resource);

    /**
     * Set empty array to 'data' section.
     *
     * @return void
     */
    public function setEmptyData();

    /**
     * Set null to 'data' section.
     *
     * @return void
     */
    public function setNullData();

    /**
     * Add a link to resource in 'data' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addLinkToData(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    );

    /**
     * Add a reference to resource in 'data' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     *
     * @return void
     */
    public function addReferenceToData(ResourceObjectInterface $parent, LinkObjectInterface $current);

    /**
     * Add an empty link to resource in 'data' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     *
     * @return void
     */
    public function addEmptyLinkToData(ResourceObjectInterface $parent, LinkObjectInterface $current);

    /**
     * Add a null link to resource in 'data' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     *
     * @return void
     */
    public function addNullLinkToData(ResourceObjectInterface $parent, LinkObjectInterface $current);

    /**
     * Add resource to 'included' section.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addToIncluded(ResourceObjectInterface $resource);

    /**
     * Add a link to resource in 'included' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $link
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addLinkToIncluded(
        ResourceObjectInterface $parent,
        LinkObjectInterface $link,
        ResourceObjectInterface $resource
    );

    /**
     * Add an empty link to resource in 'included' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     *
     * @return void
     */
    public function addEmptyLinkToIncluded(ResourceObjectInterface $parent, LinkObjectInterface $current);

    /**
     * Add a null link to resource in 'included' section.
     *
     * @param ResourceObjectInterface $parent
     * @param LinkObjectInterface     $current
     *
     * @return void
     */
    public function addNullLinkToIncluded(ResourceObjectInterface $parent, LinkObjectInterface $current);

    /**
     * Mark resource as completed (no new links will be added to the resource anymore).
     *
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function setResourceCompleted(ResourceObjectInterface $resource);

    /**
     * Add information to 'errors' top-level section.
     *
     * If you add error information no other elements will be in output document.
     *
     * @param ErrorInterface $error
     *
     * @return void
     */
    public function addError(ErrorInterface $error);

    /**
     * Get document as array.
     *
     * @return array
     */
    public function getDocument();
}
