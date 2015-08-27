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

/**
 * @package Neomerx\JsonApi
 */
interface ResourceObjectInterface
{
    /**
     * Get resource type.
     *
     * @return string
     */
    public function getType();

    /**
     * Get resource ID.
     *
     * @return string
     */
    public function getId();

    /**
     * Get resource attributes.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * Get meta-information about resource object.
     *
     * @return array|object|null
     */
    public function getPrimaryMeta();

    /**
     * Get meta-information about resource relationships when resource is primary.
     *
     * @return array|object|null
     */
    public function getRelationshipsPrimaryMeta();

    /**
     * Get meta-information about resource object.
     *
     * @return array|object|null
     */
    public function getLinkageMeta();

    /**
     * Get meta-information about resource object.
     *
     * @return array|object|null
     */
    public function getInclusionMeta();

    /**
     * Get meta-information about resource relationships when resource is included.
     *
     * @return array|object|null
     */
    public function getRelationshipsInclusionMeta();

    /**
     * Get resource 'self' endpoint URL.
     *
     * @return LinkInterface
     */
    public function getSelfSubLink();

    /**
     * Get links related to resource.
     *
     * @return array
     */
    public function getResourceLinks();

    /**
     * Get links related to resource when it is in 'included' section.
     *
     * @return array
     */
    public function getIncludedResourceLinks();

    /**
     * If resource attributes should be shown when the resource is within 'included'.
     *
     * @return bool
     */
    public function isShowAttributesInIncluded();

    /**
     * If original data we part of an array of elements.
     *
     * @return bool
     */
    public function isInArray();
}
