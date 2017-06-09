<?php namespace Neomerx\JsonApi\Contracts\Http\Headers;

/**
 * Copyright 2015-2017 info@neomerx.com
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
interface HeaderParametersInterface
{
    /**
     * Get HTTP request method (e.g. GET, POST, etc)
     *
     * @return string
     */
    public function getMethod();

    /**
     * Get get 'Content-Type' header if request has body and `null` otherwise.
     *
     * @return HeaderInterface|null
     */
    public function getContentTypeHeader();

    /**
     * Get 'Accept' header.
     *
     * @return AcceptHeaderInterface
     */
    public function getAcceptHeader();
}
