<?php namespace Neomerx\JsonApi\Contracts\Http\Parameters;

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
interface EncodingParametersInterface
{
    /**
     * Get requested include paths.
     *
     * @return array|null
     */
    public function getIncludePaths();

    /**
     * Get filed names that should be in result.
     *
     * @return array|null
     */
    public function getFieldSets();

    /**
     * Get filed names that should be in result.
     *
     * @param string $type
     *
     * @return string[]|null
     */
    public function getFieldSet($type);
}
