<?php namespace Neomerx\JsonApi\Parameters;

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

use \Neomerx\JsonApi\Parameters\Headers\MediaType;
use \Neomerx\JsonApi\Parameters\Headers\AcceptHeader;
use \Neomerx\JsonApi\Parameters\Headers\AcceptMediaType;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\AcceptHeaderInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParametersFactory implements ParametersFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createEncodingParameters($includePaths = null, array $fieldSets = null)
    {
        return new EncodingParameters($includePaths, $fieldSets);
    }

    /**
     * @inheritdoc
     */
    public function createMediaType($type, $subType, $parameters = null)
    {
        return new MediaType($type, $subType, $parameters);
    }

    /**
     * @inheritdoc
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
    ) {
        return new Parameters(
            $contentType,
            $accept,
            $includePaths,
            $fieldSets,
            $sortParameters,
            $pagingParameters,
            $filteringParameters,
            $unrecognizedParams
        );
    }

    /**
     * @inheritdoc
     */
    public function createParametersParser()
    {
        return new ParametersParser($this);
    }

    /**
     * @inheritdoc
     */
    public function createSortParam($sortField, $isAscending)
    {
        return new SortParameter($sortField, $isAscending);
    }

    /**
     * @inheritdoc
     */
    public function createSupportedExtensions($extensions = MediaTypeInterface::NO_EXT)
    {
        return new SupportedExtensions($extensions);
    }

    /**
     * @inheritdoc
     */
    public function createAcceptMediaType(
        $position,
        $type,
        $subType,
        $parameters = null,
        $quality = 1.0,
        $extensions = null
    ) {
        return new AcceptMediaType($position, $type, $subType, $parameters, $quality, $extensions);
    }

    /**
     * @inheritdoc
     */
    public function createAcceptHeader($unsortedMediaTypes)
    {
        return new AcceptHeader($unsortedMediaTypes);
    }
}
