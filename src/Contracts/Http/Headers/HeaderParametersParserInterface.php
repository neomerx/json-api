<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Http\Headers;

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
interface HeaderParametersParserInterface
{
    /** Header name that contains format of output data from client */
    const HEADER_ACCEPT = 'Accept';

    /** Header name that contains format of input data from client */
    const HEADER_CONTENT_TYPE = 'Content-Type';

    /**
     * Parse input as `Accept` header.
     *
     * @param string $value
     *
     * @return iterable
     */
    public function parseAcceptHeader(string $value): iterable;

    /**
     * Parse input as `Content-Type` header.
     *
     * @param string $value
     *
     * @return MediaTypeInterface
     */
    public function parseContentTypeHeader(string $value): MediaTypeInterface;
}
