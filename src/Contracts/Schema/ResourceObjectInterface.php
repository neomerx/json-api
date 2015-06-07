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
     * Get meta-information about resource object.
     *
     * @return array|object|null
     */
    public function getRelationshipMeta();

    /**
     * Get meta-information about resource object.
     *
     * @return array|object|null
     */
    public function getInclusionMeta();

    /**
     * Get resource 'self' endpoint URL.
     *
     * @return LinkInterface
     */
    public function getSelfSubLink();

    /**
     * If 'self' endpoint URL should be shown for resource in 'data' section.
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
     * If resource relationships should be shown for included resources.
     *
     * @return bool
     */
    public function isShowRelationshipsInIncluded();

    /**
     * If original data we part of an array of elements.
     *
     * @return bool
     */
    public function isInArray();
}
