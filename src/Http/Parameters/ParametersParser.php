<?php namespace Neomerx\JsonApi\Http\Parameters;

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

use \InvalidArgumentException;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Http\Headers\Header;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Http\Headers\AcceptHeader;
use \Neomerx\JsonApi\Exceptions\JsonApiException as E;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParametersParser implements ParametersParserInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ParametersFactoryInterface
     */
    private $factory;

    /**
     * @param ParametersFactoryInterface $factory
     */
    public function __construct(ParametersFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function parse(ServerRequestInterface $request)
    {
        $acceptHeader      = null;
        $contentTypeHeader = null;

        try {
            $contentType = $request->getHeader(HeaderInterface::HEADER_CONTENT_TYPE);
            $contentTypeHeader = Header::parse(
                empty($contentType) === true ? MediaTypeInterface::JSON_API_MEDIA_TYPE : $contentType[0],
                HeaderInterface::HEADER_CONTENT_TYPE
            );
        } catch (InvalidArgumentException $exception) {
            E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST, $exception));
        }

        try {
            $headerString = $request->getHeader(HeaderInterface::HEADER_ACCEPT);
            if (empty($headerString) === false) {
                $acceptHeader = AcceptHeader::parse($headerString[0]);
            } else {
                $jsonMediaType = $this->factory->createAcceptMediaType(
                    0,
                    MediaTypeInterface::JSON_API_TYPE,
                    MediaTypeInterface::JSON_API_SUB_TYPE
                );
                $acceptHeader = $this->factory->createAcceptHeader([$jsonMediaType]);
            }
        } catch (InvalidArgumentException $exception) {
            E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST, $exception));
        }

        $parameters = $request->getQueryParams();

        return $this->factory->createParameters(
            $contentTypeHeader,
            $acceptHeader,
            $this->getIncludePaths($parameters),
            $this->getFieldSets($parameters),
            $this->getSortParameters($parameters),
            $this->getPagingParameters($parameters),
            $this->getFilteringParameters($parameters),
            $this->getUnrecognizedParameters($parameters)
        );
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getIncludePaths(array $parameters)
    {
        $paths  = $this->getStringParamOrNull($parameters, self::PARAM_INCLUDE);
        $result = empty($paths) === false ? explode(',', rtrim($paths, ',')) : null;

        return $result;
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getFieldSets(array $parameters)
    {
        $result    = [];
        $fieldSets = $this->getParamOrNull($parameters, self::PARAM_FIELDS);
        if (empty($fieldSets) === false && is_array($fieldSets)) {
            foreach ($fieldSets as $type => $fields) {
                // We expect fields to be comma separated or empty strings. Multi-dimension arrays are not allowed.
                is_string($fields) ?: E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST));
                $result[$type] = (empty($fields) === true ? [] : explode(',', $fields));
            }
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @param array $parameters
     *
     * @return SortParameterInterface[]|null
     */
    protected function getSortParameters(array $parameters)
    {
        $sortParams = null;
        $sortParam  = $this->getStringParamOrNull($parameters, self::PARAM_SORT);
        if ($sortParam !== null) {
            foreach (explode(',', $sortParam) as $param) {
                $isDesc = false;
                empty($param) === false ?
                    $isDesc = ($param[0] === '-') : E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST));
                $sortField = ltrim($param, '+-');
                empty($sortField) === false ?: E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST));
                $sortParams[] = $this->factory->createSortParam($sortField, $isDesc === false);
            }
        }
        return $sortParams;
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getPagingParameters(array $parameters)
    {
        return $this->getArrayParamOrNull($parameters, self::PARAM_PAGE);
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getFilteringParameters(array $parameters)
    {
        return $this->getArrayParamOrNull($parameters, self::PARAM_FILTER);
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getUnrecognizedParameters(array $parameters)
    {
        $supported = [
            self::PARAM_INCLUDE => 0,
            self::PARAM_FIELDS  => 0,
            self::PARAM_PAGE    => 0,
            self::PARAM_FILTER  => 0,
            self::PARAM_SORT    => 0,
        ];
        $unrecognized = array_diff_key($parameters, $supported);
        return empty($unrecognized) === true ? null : $unrecognized;
    }

    /**
     * @param array $parameters
     * @param string $name
     *
     * @return array|null
     */
    private function getArrayParamOrNull(array $parameters, $name)
    {
        $value = $this->getParamOrNull($parameters, $name);

        $isArrayOrNull = ($value === null || is_array($value) === true);
        $isArrayOrNull === true ?: E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST));

        return $value;
    }

    /**
     * @param array $parameters
     * @param string $name
     *
     * @return string|null
     */
    private function getStringParamOrNull(array $parameters, $name)
    {
        $value = $this->getParamOrNull($parameters, $name);

        $isStringOrNull = ($value === null || is_string($value) === true);
        $isStringOrNull === true ?: E::throwException(new E([], E::HTTP_CODE_BAD_REQUEST));

        return $value;
    }

    /**
     * @param array  $parameters
     * @param string $name
     *
     * @return mixed
     */
    private function getParamOrNull(array $parameters, $name)
    {
        return isset($parameters[$name]) === true ? $parameters[$name] : null;
    }
}
