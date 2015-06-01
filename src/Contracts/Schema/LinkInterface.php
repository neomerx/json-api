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
 * Unless required by applicable law or agreed for in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;

/**
 * @package Neomerx\JsonApi
 */
interface LinkInterface
{
    /** Reserved keyword */
    const SELF    = DocumentInterface::KEYWORD_SELF;
    /** Reserved keyword */
    const RELATED = DocumentInterface::KEYWORD_RELATED;
    /** Reserved keyword */
    const FIRST   = DocumentInterface::KEYWORD_FIRST;
    /** Reserved keyword */
    const LAST    = DocumentInterface::KEYWORD_LAST;
    /** Reserved keyword */
    const NEXT    = DocumentInterface::KEYWORD_NEXT;
    /** Reserved keyword */
    const PREV    = DocumentInterface::KEYWORD_PREV;
    /** Reserved keyword */
    const ABOUT   = 'about';

    /**
     * Get 'href' (URL) value.
     *
     * @return string
     */
    public function getSubHref();

    /**
     * Get meta information.
     *
     * @return array|object|null
     */
    public function getMeta();

    /**
     * If $subHref is a full URL and must not be concatenated with other URLs.
     *
     * @return bool
     */
    public function isTreatAsHref();
}
