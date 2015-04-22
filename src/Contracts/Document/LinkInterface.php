<?php namespace Neomerx\JsonApi\Contracts\Document;

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
interface LinkInterface
{
    /**
     * Get link name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get type of link object.
     *
     * @return string
     */
    public function getType();

    /**
     * Get 'self' URL of link object.
     *
     * @return string
     */
    public function getSelfUrl();

    /**
     * If 'self' endpoint should be shown.
     *
     * @return bool
     */
    public function isShowSelf();

    /**
     * Get 'related' URL of link object.
     *
     * @return string
     */
    public function getRelatedUrl();

    /**
     * If 'related' endpoint should be shown.
     *
     * @return bool
     */
    public function isShowRelated();

    /**
     * If link should be shown as 'related' URL only.
     *
     * @return bool
     */
    public function isOnlyRelated();

    /**
     * Get linkage identities.
     *
     * @return int[]|string[]
     */
    public function getLinkageIds();

    /**
     * If 'meta' should be shown.
     *
     * @return bool
     */
    public function isShowMeta();

    /**
     * Get meta-information about link object.
     *
     * @return array|object|null
     */
    public function getMeta();
}
