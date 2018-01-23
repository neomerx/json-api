<?php namespace Neomerx\JsonApi\Contracts\Http;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;

/**
 * @package Neomerx\JsonApi
 */
interface HttpFactoryInterface
{
    /**
     * Create parameter analyzer.
     *
     * @param EncodingParametersInterface $parameters
     * @param ContainerInterface          $container
     *
     * @return ParametersAnalyzerInterface
     */
    public function createParametersAnalyzer(
        EncodingParametersInterface $parameters,
        ContainerInterface $container
    ): ParametersAnalyzerInterface;

    /**
     * Create media type.
     *
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     *
     * @return MediaTypeInterface
     */
    public function createMediaType(string $type, string $subType, array $parameters = null): MediaTypeInterface;

    /**
     * Create parameters.
     *
     * @param string[]|null $includePaths
     * @param array|null    $fieldSets
     *
     * @return EncodingParametersInterface
     */
    public function createQueryParameters(
        array $includePaths = null,
        array $fieldSets = null
    ): EncodingParametersInterface;

    /**
     * @param string                $method
     * @param AcceptHeaderInterface $accept
     * @param HeaderInterface       $contentType
     *
     * @return HeaderParametersInterface
     */
    public function createHeaderParameters(
        string $method,
        AcceptHeaderInterface $accept,
        HeaderInterface $contentType
    ): HeaderParametersInterface;

    /**
     * @param string                $method
     * @param AcceptHeaderInterface $accept
     *
     * @return HeaderParametersInterface
     */
    public function createNoContentHeaderParameters(
        string $method,
        AcceptHeaderInterface $accept
    ): HeaderParametersInterface;

    /**
     * Create parameters parser.
     *
     * @return HeaderParametersParserInterface
     */
    public function createHeaderParametersParser(): HeaderParametersParserInterface;

    /**
     * Create media type for Accept HTTP header.
     *
     * @param int    $position
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     * @param float  $quality
     *
     * @return AcceptMediaTypeInterface
     */
    public function createAcceptMediaType(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0
    ): AcceptMediaTypeInterface;

    /**
     * Create Accept HTTP header.
     *
     * @param AcceptMediaTypeInterface[] $unsortedMediaTypes
     *
     * @return AcceptHeaderInterface
     */
    public function createAcceptHeader(array $unsortedMediaTypes): AcceptHeaderInterface;
}
