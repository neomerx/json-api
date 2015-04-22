<?php namespace Neomerx\JsonApi\Contracts\Server;

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

use \Closure;

/**
 * @package Neomerx\JsonApi
 */
interface EndPointInterface
{
    /** HTTP method */
    const HTTP_METHOD_CONNECT = 'CONNECT';
    /** HTTP method */
    const HTTP_METHOD_DELETE = 'DELETE';
    /** HTTP method */
    const HTTP_METHOD_GET = 'GET';
    /** HTTP method */
    const HTTP_METHOD_HEAD = 'HEAD';
    /** HTTP method */
    const HTTP_METHOD_OPTIONS = 'OPTIONS';
    /** HTTP method */
    const HTTP_METHOD_PATCH = 'PATCH';
    /** HTTP method */
    const HTTP_METHOD_POST = 'POST';
    /** HTTP method */
    const HTTP_METHOD_PUT = 'PUT';
    /** HTTP method */
    const HTTP_METHOD_TRACE = 'TRACE';

    /**
     * Get HTTP method (GET, POST, PUT, etc).
     *
     * @return string
     */
    public function getHttpMethod();

    /**
     * Get HTTP URL.
     *
     * @return string
     */
    public function getHttpUrl();

    /**
     * @return Closure
     */
    public function getHandler();
}
