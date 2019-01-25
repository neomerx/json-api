<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Schema;

/**
 * Copyright 2015-2019 info@neomerx.com
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
interface DocumentInterface
{
    /** Reserved keyword */
    public const KEYWORD_LINKS = 'links';
    /** Reserved keyword */
    public const KEYWORD_HREF = 'href';
    /** Reserved keyword */
    public const KEYWORD_RELATIONSHIPS = 'relationships';
    /** Reserved keyword */
    public const KEYWORD_SELF = 'self';
    /** Reserved keyword */
    public const KEYWORD_FIRST = 'first';
    /** Reserved keyword */
    public const KEYWORD_LAST = 'last';
    /** Reserved keyword */
    public const KEYWORD_NEXT = 'next';
    /** Reserved keyword */
    public const KEYWORD_PREV = 'prev';
    /** Reserved keyword */
    public const KEYWORD_RELATED = 'related';
    /** Reserved keyword */
    public const KEYWORD_TYPE = 'type';
    /** Reserved keyword */
    public const KEYWORD_ID = 'id';
    /** Reserved keyword */
    public const KEYWORD_ATTRIBUTES = 'attributes';
    /** Reserved keyword */
    public const KEYWORD_META = 'meta';
    /** Reserved keyword */
    public const KEYWORD_ALIASES = 'aliases';
    /** Reserved keyword */
    public const KEYWORD_PROFILE = 'profile';
    /** Reserved keyword */
    public const KEYWORD_DATA = 'data';
    /** Reserved keyword */
    public const KEYWORD_INCLUDED = 'included';
    /** Reserved keyword */
    public const KEYWORD_JSON_API = 'jsonapi';
    /** Reserved keyword */
    public const KEYWORD_VERSION = 'version';

    /** Reserved keyword */
    public const KEYWORD_ERRORS = 'errors';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_ID = 'id';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_TYPE = 'type';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_STATUS = 'status';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_CODE = 'code';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_TITLE = 'title';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_DETAIL = 'detail';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_META = 'meta';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_SOURCE = 'source';
    /** Reserved keyword */
    public const KEYWORD_ERRORS_ABOUT = 'about';

    /** Include path separator */
    public const PATH_SEPARATOR = '.';
}
