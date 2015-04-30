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
     * Get resource's link objects.
     *
     * @param object $resource
     *
     * @return Iterator LinkObjectInterface[]
     */
    public function getLinkObjectIterator($resource);

    /**
     * Get resource meta information.
     *
     * @param object|array $data
     *
     * @return mixed
     */
    public function getMeta($data);

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
    public function isShowLinksInIncluded();

    /**
     * If 'meta' should be shown for included resources.
     *
     * @return bool
     */
    public function isShowMetaInIncluded();

    /**
     * Get default depth for object inclusion to 'include' section.
     * If any other setting is not available this value will be used as a limiter.
     *
     * @return int
     */
    public function getDefaultParseDepth();

    /**
     * Create resource object.
     *
     * @param object        $resource
     * @param bool          $isOriginallyArrayed
     * @param string[]|null $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject($resource, $isOriginallyArrayed, array $attributeKeysFilter = null);
}
