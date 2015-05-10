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
interface ParametersFactoryInterface
{
    /**
     * Create encoding parameters.
     *
     * @param string[]|null $includePaths
     * @param array|null    $fieldSets
     *
     * @return EncodingParametersInterface
     */
    public function createEncodingParameters($includePaths = null, array $fieldSets = null);

    /**
     * Create media type.
     *
     * @param string      $mediaType
     * @param string|null $extensions
     *
     * @return MediaTypeInterface
     */
    public function createMediaType($mediaType, $extensions);

    /**
     * Create parameters.
     *
     * @param MediaTypeInterface            $inputType
     * @param MediaTypeInterface            $outputType
     * @param string[]|null                 $includePaths
     * @param array|null                    $fieldSets
     * @param SortParameterInterface[]|null $sortParameters
     * @param array|null                    $pagingParameters
     * @param array|null                    $filteringParameters
     * @param array|null                    $unrecognizedParams
     *
     * @return ParametersInterface
     */
    public function createParameters(
        MediaTypeInterface $inputType,
        MediaTypeInterface $outputType,
        array $includePaths = null,
        array $fieldSets = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    );

    /**
     * Create parameters parser.
     *
     * @return ParametersParserInterface
     */
    public function createParametersParser();

    /**
     * @param string $sortField
     * @param bool   $isAscending
     *
     * @return SortParameterInterface
     */
    public function createSortParam($sortField, $isAscending);
}
