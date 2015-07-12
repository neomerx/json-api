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
     * Get link name (null for root).
     *
     * @return string|null
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
     * @return array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    public function getLinks();

    /**
     * Get meta.
     *
     * @return mixed
     */
    public function getMeta();

    /**
     * If 'data' should be shown.
     *
     * @return bool
     */
    public function isShowData();

    /**
     * If relationship is from root (non existing root element).
     *
     * @return bool
     */
    public function isRoot();
}
