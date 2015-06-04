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

use \InvalidArgumentException;
use \Neomerx\JsonApi\Parameters\Headers\Header;
use \Neomerx\JsonApi\Parameters\Headers\AcceptHeader;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParametersParser implements ParametersParserInterface
{
    /**
     * @var ParametersFactoryInterface
     */
    private $factory;

    /**
     * @var ExceptionThrowerInterface
     */
    private $exceptionThrower;

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
    public function parse(CurrentRequestInterface $request, ExceptionThrowerInterface $exceptionThrower)
    {
        $this->exceptionThrower = $exceptionThrower;

        $acceptHeader      = null;
        $contentTypeHeader = null;

        try {
            $contentTypeHeader = Header::parse(
                $request->getHeader(HeaderInterface::HEADER_CONTENT_TYPE),
                HeaderInterface::HEADER_CONTENT_TYPE
            );
        } catch (InvalidArgumentException $exception) {
            $this->exceptionThrower->throwBadRequest();
        }

        try {
            $headerString = $request->getHeader(HeaderInterface::HEADER_ACCEPT);
            if (empty($headerString) === false) {
                $acceptHeader = AcceptHeader::parse($headerString);
            } else {
                $jsonMediaType = $this->factory->createAcceptMediaType(
                    0,
                    MediaTypeInterface::JSON_API_TYPE,
                    MediaTypeInterface::JSON_API_SUB_TYPE
                );
                $acceptHeader = $this->factory->createAcceptHeader([$jsonMediaType]);
            }
        } catch (InvalidArgumentException $exception) {
            $this->exceptionThrower->throwBadRequest();
        }

        $parameters = $request->getQueryParameters();

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
        $paths = $this->getParamOrNull($parameters, self::PARAM_INCLUDE);
        return (empty($paths) === true ? null : explode(',', rtrim($paths, ',')));
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
                // We expect fields to be comma separated strings. Multi-dimension arrays are not allowed.
                is_string($fields) ?: $this->exceptionThrower->throwBadRequest();
                // do not parse and add empty fields
                empty($fields) === true ?: $result[$type] = explode(',', $fields);
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
        $sortParam  = $this->getParamOrNull($parameters, self::PARAM_SORT);
        if ($sortParam !== null) {
            foreach (explode(',', $sortParam) as $param) {
                $isDesc = false;
                empty($param) === false ? $isDesc = ($param[0] === '-') : $this->exceptionThrower->throwBadRequest();
                $sortField = ltrim($param, '+-');
                empty($sortField) === false ?: $this->exceptionThrower->throwBadRequest();
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
        return $this->getParamOrNull($parameters, self::PARAM_PAGE);
    }

    /**
     * @param array $parameters
     *
     * @return array|null
     */
    private function getFilteringParameters(array $parameters)
    {
        return $this->getParamOrNull($parameters, self::PARAM_FILTER);
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
