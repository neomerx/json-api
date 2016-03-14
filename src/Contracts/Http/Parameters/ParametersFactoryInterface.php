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

use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;

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
     * Create parameter analyzer.
     *
     * @param EncodingParametersInterface $parameters
     * @param ContainerInterface          $container
     *
     * @return ParametersAnalyzerInterface
     */
    public function createParametersAnalyzer(EncodingParametersInterface $parameters, ContainerInterface $container);

    /**
     * Create media type.
     *
     * @param string                    $type
     * @param string                    $subType
     * @param array<string,string>|null $parameters
     *
     * @return MediaTypeInterface
     */
    public function createMediaType($type, $subType, $parameters = null);

    /**
     * Create parameters.
     *
     * @param HeaderInterface               $contentType
     * @param AcceptHeaderInterface         $accept
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
        HeaderInterface $contentType,
        AcceptHeaderInterface $accept,
        $includePaths = null,
        array $fieldSets = null,
        $sortParameters = null,
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
     * Create sort parameter.
     *
     * @param string $sortField
     * @param bool   $isAscending
     *
     * @return SortParameterInterface
     */
    public function createSortParam($sortField, $isAscending);

    /**
     * Create supported extensions.
     *
     * @param string $extensions
     *
     * @return SupportedExtensionsInterface
     */
    public function createSupportedExtensions($extensions = MediaTypeInterface::NO_EXT);

    /**
     * Create media type for Accept HTTP header.
     *
     * @param int                       $position
     * @param string                    $type
     * @param string                    $subType
     * @param array<string,string>|null $parameters
     * @param float                     $quality
     * @param array<string,string>|null $extensions
     *
     * @return AcceptMediaTypeInterface
     */
    public function createAcceptMediaType(
        $position,
        $type,
        $subType,
        $parameters = null,
        $quality = 1.0,
        $extensions = null
    );

    /**
     * Create Accept HTTP header.
     *
     * @param AcceptMediaTypeInterface[] $unsortedMediaTypes
     *
     * @return AcceptHeaderInterface
     */
    public function createAcceptHeader($unsortedMediaTypes);

    /**
     * Create header parameters checker.
     *
     * @param CodecMatcherInterface $codecMatcher
     *
     * @return HeadersCheckerInterface
     */
    public function createHeadersChecker(CodecMatcherInterface $codecMatcher);

    /**
     * Create query parameters checker.
     *
     * @param bool|false $allowUnrecognized
     * @param array|null $includePaths
     * @param array|null $fieldSetTypes
     * @param array|null $sortParameters
     * @param array|null $pagingParameters
     * @param array|null $filteringParameters
     *
     * @return QueryCheckerInterface
     */
    public function createQueryChecker(
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    );

    /**
     * Create parameters checker for headers and query.
     *
     * @param CodecMatcherInterface $codecMatcher
     * @param bool|false            $allowUnrecognized
     * @param array|null            $includePaths
     * @param array|null            $fieldSetTypes
     * @param array|null            $sortParameters
     * @param array|null            $pagingParameters
     * @param array|null            $filteringParameters
     *
     * @return ParametersCheckerInterface
     */
    public function createParametersChecker(
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = true,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    );
}
