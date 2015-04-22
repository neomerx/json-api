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
     * Get a  unique identifier for this particular occurrence of the problem.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Get a URI that may yield further details about this particular occurrence of the problem.
     *
     * @return string|null
     */
    public function getHref();

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
     * Get an array of JSON Pointers [RFC6901] to the associated resource(s) within the request document.
     *
     * @return string[]|null
     */
    public function getLinks();

    /**
     * Get an array of JSON Pointers [RFC6901] to the relevant attribute(s) within the associated resource(s)
     * in the request document.
     *
     * @return string[]|null
     */
    public function getPaths();

    /**
     * Get additional members.
     *
     * @return array|null
     */
    public function getAdditionalMembers();
}
