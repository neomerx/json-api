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
     * Get resource data from relationship.
     *
     * @return object|array|null
     */
    public function getData();

    /**
     * Get links.
     *
     * @return array<string,LinkInterface>
     */
    public function getLinks();

    /**
     * Get links.
     *
     * @param string $key
     *
     * @return LinkInterface|null
     */
    public function getLink($key);

    /**
     * Get meta.
     *
     * @return mixed
     */
    public function getMeta();

    /**
     * If 'self' endpoint URL should be shown.
     *
     * @return bool
     */
    public function isShowSelf();

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
     * If 'meta' should be shown.
     *
     * @return bool
     */
    public function isShowMeta();

    /**
     * If link should be shown as URL reference ('related').
     *
     * @return bool
     */
    public function isShowAsReference();
}
