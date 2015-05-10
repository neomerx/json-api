<?php namespace Neomerx\JsonApi\Contracts\Parameters;

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
interface ParametersInterface extends EncodingParametersInterface
{
    /**
     * Get media type of input data.
     *
     * @return MediaTypeInterface
     */
    public function getInputMediaType();

    /**
     * Get requested media type for output data.
     *
     * @return MediaTypeInterface
     */
    public function getOutputMediaType();

    /**
     * Get sort parameters.
     *
     * @return SortParameterInterface[]|null
     */
    public function getSortParameters();

    /**
     * Get pagination parameters.
     *
     * Pagination parameters are not detailed in the specification however a keyword 'page' is reserved for pagination.
     * This method returns key and value pairs from input 'page' parameter.
     *
     * @return array|null
     */
    public function getPaginationParameters();

    /**
     * Get filtering parameters.
     *
     * Filtering parameters are not detailed in the specification however a keyword 'filter' is reserved for filtering.
     * This method returns key and value pairs from input 'filter' parameter.
     *
     * @return array|null
     */
    public function getFilteringParameters();

    /**
     * Get top level parameters that have not been recognized by parser.
     *
     * @return array|null
     */
    public function getUnrecognizedParameters();
}
