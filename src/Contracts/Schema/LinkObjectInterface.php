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
interface LinkObjectInterface
{
    /**
     * Get link name.
     *
     * @return string
     */
    public function getName();

    /**
     * If 'self' endpoint URL should be shown.
     *
     * @return bool
     */
    public function isShowSelf();

    /**
     * Get 'self' URL of link object.
     *
     * @return string
     */
    public function getSelfSubUrl();

    /**
     * If link should be shown as URL reference ('related').
     *
     * @return bool
     */
    public function isShowAsReference();

    /**
     * If 'related' endpoint URL should be shown.
     *
     * @return bool
     */
    public function isShowRelated();

    /**
     * If 'linkage' should be shown.
     *
     * @return bool
     */
    public function isShowLinkage();

    /**
     * Get 'related' URL of link object.
     *
     * @return string
     */
    public function getRelatedSubUrl();

    /**
     * If link object should be included.
     *
     * @return bool
     */
    public function isShouldBeIncluded();

    /**
     * If 'meta' should be shown.
     *
     * @return bool
     */
    public function isShowMeta();

    /**
     * If pagination information should be shown.
     *
     * @return bool
     */
    public function isShowPagination();

    /**
     * Get linked resource data.
     *
     * @return object|array|null
     */
    public function getLinkedData();

    /**
     * Get 'self' controller data.
     *
     * @return mixed
     */
    public function getSelfControllerData();

    /**
     * Get 'related' controller data.
     *
     * @return mixed
     */
    public function getRelatedControllerData();

    /**
     * Get pagination information.
     *
     * @return PaginationLinksInterface|null
     */
    public function getPagination();
}
