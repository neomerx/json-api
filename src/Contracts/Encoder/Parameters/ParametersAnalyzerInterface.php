<?php namespace Neomerx\JsonApi\Contracts\Encoder\Parameters;

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

use \Neomerx\JsonApi\Contracts\Http\Parameters\EncodingParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
interface ParametersAnalyzerInterface
{
    /**
     * Get parameters.
     *
     * @return EncodingParametersInterface
     */
    public function getParameters();

    /**
     * If path is included.
     *
     * @param string $path
     * @param string $type
     *
     * @return bool
     */
    public function isPathIncluded($path, $type);

    /**
     * Get a list of relationship that should be included for $path of root type $type.
     *
     * @param string $path
     * @param string $type
     *
     * @return string[]
     */
    public function getIncludeRelationships($path, $type);

    /**
     * If field-sets allows any fields to be in output (field-set filter is not empty array).
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasSomeFields($type);
}
