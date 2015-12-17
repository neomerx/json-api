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
interface ErrorInterface
{
    /**
     * Key for 'Source' field.
     */
    const SOURCE_POINTER = 'pointer';

    /**
     * Key for 'Source' field.
     */
    const SOURCE_PARAMETER = 'parameter';

    /**
     * Get a unique identifier for this particular occurrence of the problem.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Get links that may lead to further details about this particular occurrence of the problem.
     *
     * @return null|array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    public function getLinks();

    /**
     * Get the HTTP status code applicable to this problem, expressed as a string value.
     *
     * @return string|null
     */
    public function getStatus();

    /**
     * Get an application-specific error code, expressed as a string value.
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get a short, human-readable summary of the problem.
     *
     * It should not change from occurrence to occurrence of the problem, except for purposes of localization.
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get a human-readable explanation specific to this occurrence of the problem.
     *
     * @return string|null
     */
    public function getDetail();

    /**
     * An object containing references to the source of the error, optionally including any of the following members:
     *    "pointer"   - A JSON Pointer [RFC6901] to the associated entity in the request document
     *                  [e.g. "/data" for a primary data object, or "/data/attributes/title" for a specific attribute].
     *    "parameter" - An optional string indicating which query parameter caused the error.
     *
     * @return array|null
     */
    public function getSource();

    /**
     * Get error meta information.
     *
     * @return array|null
     */
    public function getMeta();
}
