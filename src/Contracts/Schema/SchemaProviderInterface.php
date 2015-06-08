<?php namespace Neomerx\JsonApi\Contracts\Schema;

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

use \Iterator;

/**
 * @package Neomerx\JsonApi
 */
interface SchemaProviderInterface
{
    /**
     * Get resource type.
     *
     * @return string
     */
    public function getResourceType();

    /**
     * Get resource identity.
     *
     * @param object $resource
     *
     * @return string
     */
    public function getId($resource);

    /**
     * Get resource URL.
     *
     * @param object $resource
     *
     * @return LinkInterface
     */
    public function getSelfSubLink($resource);

    /**
     * Get resource attributes.
     *
     * @param object $resource
     *
     * @return array
     */
    public function getAttributes($resource);

    /**
     * Create resource object.
     *
     * @param object                   $resource
     * @param bool                     $isOriginallyArrayed
     * @param array <string, int>|null $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject($resource, $isOriginallyArrayed, $attributeKeysFilter = null);

    /**
     * Get resource's relationship objects.
     *
     * @param object $resource
     *
     * @return Iterator LinkObjectInterface[]
     */
    public function getRelationshipObjectIterator($resource);

    /**
     * If 'self' endpoint URL.
     *
     * @return bool
     */
    public function isShowSelf();

    /**
     * If 'self' endpoint URL should be shown for included resources.
     *
     * @return bool
     */
    public function isShowSelfInIncluded();

    /**
     * If resource attributes should be shown when the resource is within 'included'.
     *
     * @return bool
     */
    public function isShowAttributesInIncluded();

    /**
     * If links be shown for included resources.
     *
     * @return bool
     */
    public function isShowRelationshipsInIncluded();

    /**
     * Get schema default include paths.
     *
     * @return string[]
     */
    public function getIncludePaths();

    /**
     * Get meta when resource is primary (top level 'data' section).
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getPrimaryMeta($resource);

    /**
     * Get meta when resource is within included resources.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getInclusionMeta($resource);

    /**
     * Get get relationships meta when the resource is primary.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getRelationshipsPrimaryMeta($resource);

    /**
     * Get get relationships meta when the resource is within included.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getRelationshipsInclusionMeta($resource);

    /**
     * Get meta when resource is within relationship of a primary resource.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getLinkageMeta($resource);
}
