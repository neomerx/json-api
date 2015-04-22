<?php namespace Neomerx\JsonApi\Contracts\Encoder;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed for in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * @package Neomerx\JsonApi
 */
interface DocumentLinksInterface
{
    /**
     * Get 'self' URL for top-level 'links' section.
     *
     * @return string|null
     */
    public function getSelfUrl();

    /**
     * Get 'first' URL for top-level 'links' section.
     *
     * @return string|null
     */
    public function getFirstUrl();

    /**
     * Get 'last' URL for top-level 'links' section.
     *
     * @return string|null
     */
    public function getLastUrl();

    /**
     * Get 'prev' URL for top-level 'links' section.
     *
     * @return string|null
     */
    public function getPrevUrl();

    /**
     * Get 'next' URL for top-level 'links' section.
     *
     * @return string|null
     */
    public function getNextUrl();
}
