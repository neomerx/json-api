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
interface RelationshipObjectInterface
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
     * @return LinkInterface
     */
    public function getSelfLink();

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
     * If 'data' should be shown.
     *
     * @return bool
     */
    public function isShowData();

    /**
     * Get 'related' URL of link object.
     *
     * @return LinkInterface
     */
    public function getRelatedLink();

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
     * Get resource data from relationship.
     *
     * @return object|array|null
     */
    public function getData();

    /**
     * Get pagination information.
     *
     * @return PaginationLinksInterface|null
     */
    public function getPagination();
}
