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
     * @return string
     */
    public function getSelfUrl($resource);

    /**
     * Get resource attributes.
     *
     * @param object $resource
     *
     * @return array
     */
    public function getAttributes($resource);

    /**
     * Get resource's relationship objects.
     *
     * @param object $resource
     *
     * @return Iterator LinkObjectInterface[]
     */
    public function getRelationshipObjectIterator($resource);

    /**
     * Get resource meta information.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getMeta($resource);

    /**
     * If 'self' endpoint URL.
     *
     * @return bool
     */
    public function isShowSelf();

    /**
     * If 'meta' should be shown for resource.
     *
     * @return bool
     */
    public function isShowMeta();

    /**
     * If 'self' endpoint URL should be shown for included resources.
     *
     * @return bool
     */
    public function isShowSelfInIncluded();

    /**
     * If links be shown for included resources.
     *
     * @return bool
     */
    public function isShowRelationshipsInIncluded();

    /**
     * If 'meta' should be shown for included resources.
     *
     * @return bool
     */
    public function isShowMetaInIncluded();

    /**
     * If 'meta' should be shown in relationships.
     *
     * @return bool
     */
    public function isShowMetaInRelationships();

    /**
     * Create resource object.
     *
     * @param object $resource
     * @param bool   $isOriginallyArrayed
     * @param        array <string, int>|null $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject($resource, $isOriginallyArrayed, array $attributeKeysFilter = null);

    /**
     * Get schema default include paths.
     *
     * @return string[]
     */
    public function getIncludePaths();
}
