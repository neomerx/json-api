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

use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
interface DocumentInterface
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
    const KEYWORD_JSON_API      = 'jsonapi';
    /** Reserved keyword */
    const KEYWORD_VERSION       = 'version';

    /** Reserved keyword */
    const KEYWORD_ERRORS        = 'errors';
    /** Reserved keyword */
    const KEYWORD_ERRORS_ID     = 'id';
    /** Reserved keyword */
    const KEYWORD_ERRORS_LINKS  = self::KEYWORD_LINKS;
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
    /** Reserved keyword */
    const KEYWORD_ERRORS_ABOUT  = 'about';
    /** Include path separator */
    const PATH_SEPARATOR        = '.';

    /**
     * Set URLs to top-level 'links' section.
     *
     * @param array<string,LinkInterface>|null $links
     *
     * @return void
     */
    public function setDocumentLinks($links);

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
     * Add an empty relationship to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     *
     * @return void
     */
    public function addEmptyRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    );

    /**
     * Add a null relationship to resource in 'data' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     *
     * @return void
     */
    public function addNullRelationshipToData(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    );

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
     * Add an empty relationship to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     *
     * @return void
     */
    public function addEmptyRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
    );

    /**
     * Add a null relationship to resource in 'included' section.
     *
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relationship
     *
     * @return void
     */
    public function addNullRelationshipToIncluded(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relationship
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
     * Add JSON API version information.
     *
     * @link http://jsonapi.org/format/#document-jsonapi-object
     *
     * @param string     $version
     * @param mixed|null $meta
     *
     * @return void
     */
    public function addJsonApiVersion($version, $meta = null);

    /**
     * Set a prefix that will be applied to all URLs in the document except marked as href.
     *
     * @see LinkInterface
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setUrlPrefix($prefix);

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
