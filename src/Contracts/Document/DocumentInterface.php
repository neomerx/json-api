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

use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

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
     * Add a relationship to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     * @param ResourceObjectInterface     $resource
     *
     * @return void
     */
    public function addRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship,
        ResourceObjectInterface $resource
    );

    /**
     * Add a reference to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addReferenceToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current);

    /**
     * Add an empty relationship to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addEmptyRelationshipToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current);

    /**
     * Add a null relationship to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addNullRelationshipToData(ResourceObjectInterface $parent, RelationshipObjectInterface $current);

    /**
     * Add resource to 'included' section.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function addToIncluded(ResourceObjectInterface $resource);

    /**
     * Add a relationship to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     * @param ResourceObjectInterface     $resource
     *
     * @return void
     */
    public function addRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship,
        ResourceObjectInterface $resource
    );

    /**
     * Add a reference to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addReferenceToIncluded(ResourceObjectInterface $parent, RelationshipObjectInterface $current);

    /**
     * Add an empty relationship to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addEmptyRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $current
    );

    /**
     * Add a null relationship to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $current
     *
     * @return void
     */
    public function addNullRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $current
    );

    /**
     * Mark resource as completed (no new relations/links/etc will be added to the resource anymore).
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
     * Remove 'data' top-level section.
     *
     * @return void
     */
    public function unsetData();

    /**
     * Get document as array.
     *
     * @return array
     */
    public function getDocument();
}
