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
    public function getMeta();

    /**
     * Get resource 'self' endpoint URL.
     *
     * @return string
     */
    public function getSelfUrl();

    /**
     * If 'self' endpoint URL should be shown for resource in 'data' section.
     *
     * @return bool
     */
    public function isShowSelf();

    /**
     * If 'meta' should be shown for resource in 'data' section.
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
     * If 'meta' should be shown for included resources.
     *
     * @return bool
     */
    public function isShowMetaInIncluded();

    /**
     * If 'meta' should be shown in linkages.
     *
     * @return bool
     */
    public function isShowMetaInLinkage();

    /**
     * If resources links should be shown for included resources.
     *
     * @return bool
     */
    public function isShowLinksInIncluded();

    /**
     * If original data we part of an array of elements.
     *
     * @return bool
     */
    public function isInArray();
}
