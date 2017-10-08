<?php namespace Neomerx\JsonApi\Http\Query;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\I18n\Translator as T;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiException as E;
use \Neomerx\JsonApi\Contracts\Http\HttpFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;

/**
 * @package Neomerx\JsonApi
 */
class QueryParametersParser implements QueryParametersParserInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var HttpFactoryInterface
     */
    private $factory;

    /**
     * @param HttpFactoryInterface $factory
     */
    public function __construct(HttpFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function parse(ServerRequestInterface $request)
    {
        return $this->parseQueryParameters($request->getQueryParams());
    }

    /**
     * @inheritdoc
     */
    public function parseQueryParameters(array $parameters)
    {
        return $this->factory->createQueryParameters(
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
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getFieldSets(array $parameters)
    {
        $result = null;
        $fieldSets = $this->getParamOrNull($parameters, self::PARAM_FIELDS);
        if (empty($fieldSets) === false && is_array($fieldSets)) {
            foreach ($fieldSets as $type => $fields) {
                // We expect fields to be comma separated or empty strings. Multi-dimension arrays are not allowed.
                if (is_string($fields) === false) {
                    $detail = T::t(self::PARAM_FIELDS . ' parameter values should be comma separated strings.');
                    throw new E($this->createInvalidQueryErrors($detail), E::HTTP_CODE_BAD_REQUEST);
                }
                $result[$type] = (empty($fields) === true ? [] : explode(',', $fields));
            }
        }

        return $result;
    }

    /**
     * @param array $parameters
     *
     * @return SortParameterInterface[]|null
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getSortParameters(array $parameters)
    {
        $sortParams = null;
        $sortParam  = $this->getStringParamOrNull($parameters, self::PARAM_SORT);
        if ($sortParam !== null) {
            foreach (explode(',', $sortParam) as $param) {
                if (empty($param) === true) {
                    $detail = T::t('Parameter ' . self::PARAM_SORT . ' should have valid value specified.');
                    throw new E($this->createInvalidQueryErrors($detail), E::HTTP_CODE_BAD_REQUEST);
                }
                $isDesc = $isDesc = ($param[0] === '-');
                $sortField = ltrim($param, '+-');
                if (empty($sortField) === true) {
                    $detail = T::t('Parameter ' . self::PARAM_SORT . ' should have valid name specified.');
                    throw new E($this->createInvalidQueryErrors($detail), E::HTTP_CODE_BAD_REQUEST);
                }
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
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getArrayParamOrNull(array $parameters, $name)
    {
        $value = $this->getParamOrNull($parameters, $name);

        if ($value !== null && is_array($value) === false) {
            $detail = T::t('Value should be either an array or null.');
            throw new E($this->createParamErrors($name, $detail), E::HTTP_CODE_BAD_REQUEST);
        }

        return $value;
    }

    /**
     * @param array $parameters
     * @param string $name
     *
     * @return string|null
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getStringParamOrNull(array $parameters, $name)
    {
        $value = $this->getParamOrNull($parameters, $name);

        if ($value !== null && is_string($value) === false) {
            $detail = T::t('Value should be either a string or null.');
            throw new E($this->createParamErrors($name, $detail), E::HTTP_CODE_BAD_REQUEST);
        }

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

    /**
     * @param string $detail
     *
     * @return Error[]
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function createInvalidQueryErrors($detail)
    {
        // NOTE: external libraries might expect this method to exist and have certain signature
        // @see https://github.com/neomerx/json-api/issues/185#issuecomment-329135390

        $title = T::t('Invalid query.');

        return [
            new Error(null, null, null, null, $title, $detail),
        ];
    }

    /**
     * @param string $name
     * @param string $detail
     *
     * @return Error[]
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function createParamErrors($name, $detail)
    {
        // NOTE: external libraries might expect this method to exist and have certain signature
        // @see https://github.com/neomerx/json-api/issues/185#issuecomment-329135390

        $title  = T::t('Invalid query parameter.');
        $source = [
            Error::SOURCE_PARAMETER => $name,
        ];

        return [
            new Error(null, null, null, null, $title, $detail, $source),
        ];
    }
}
