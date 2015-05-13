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

use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParametersParser implements ParametersParserInterface
{
    /** Header name that contains format of output data from client */
    const HEADER_ACCEPT = 'Accept';

    /** Header name that contains format of input data from client */
    const HEADER_CONTENT_TYPE = 'Content-Type';

    /** Header parameter name that contains format extension names */
    const HEADER_PARAM_EXT = 'ext';

    /** Parameter name */
    const PARAM_INCLUDE = 'include';

    /** Parameter name */
    const PARAM_FIELDS = 'fields';

    /** Parameter name */
    const PARAM_PAGE = 'page';

    /** Parameter name */
    const PARAM_FILTER = 'filter';

    /** Parameter name */
    const PARAM_SORT = 'sort';

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

        $contentTypeHeader = $request->getHeader(self::HEADER_CONTENT_TYPE);
        $acceptHeader      = $request->getHeader(self::HEADER_ACCEPT);
        $parameters        = $request->getQueryParameters();

        return $this->factory->createParameters(
            $this->createMediaType($contentTypeHeader),
            $this->createMediaType($acceptHeader),
            $this->getIncludePaths($parameters),
            $this->getFieldSets($parameters),
            $this->getSortParameters($parameters),
            $this->getPagingParameters($parameters),
            $this->getFilteringParameters($parameters),
            $this->getUnrecognizedParameters($parameters)
        );
    }

    /**
     * @param string $header
     * @param string $parameterName
     *
     * @return array
     */
    protected function parseHeader($header, $parameterName)
    {
        $regexp = '/^'.
            '([^;]*)\;?'. // Match everything until ';'. The semicolon at the end is optional.
            '(?:'.        // In this non-capturing group try to find parameter or match anything

            // here we skip anything until we find parameter we want followed by '="' with possible spaces and
            // then capture everything until closing '"' then we skip all the rest ("s are optional)
            '.*?(?:'.$parameterName.'\s*=\s*(?|\"([^\"]*)\"|([^\s\",]*))).*'. // capture value in paramName = "..."
            // skip spaces and = from^   to^     ^      ^   ^         ^
            // capture value in "..." ___________|______|   |         |
            // or capture not in "s ________________________|_________| (take all till space, " or ,)

            // Thus the following formats are supported
            // 1) value
            // 2) value;
            // 3) value; ... param = paramValue ... (with or without spaces)
            // 4) value; ... param = "paramValue, ..." ... (with or without spaces)
            // Yes, I love regex too. Mostly for writing comments to them.

            '|'. // or
            '.*' // if not found then match anything but capture nothing

            .')'.
            '$/';

        $value      = null;
        $paramValue = null;
        if (preg_match($regexp, $header, $matches) === 1) {
            $value = $matches[1];
            isset($matches[2]) === false ?: $paramValue = $matches[2];
        }

        return [$value, $paramValue];
    }

    /**
     * @param $header
     *
     * @return MediaTypeInterface
     */
    protected function createMediaType($header)
    {
        list($mediaType, $typeExtensions) = $this->parseHeader($header, self::HEADER_PARAM_EXT);

        $mediaType = trim($mediaType);
        $typeExtensions === null ?: $typeExtensions = rtrim(str_replace(' ', '', $typeExtensions), ',');

        return $this->factory->createMediaType($mediaType, $typeExtensions);
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
                $result[$type] = explode(',', $fields);
            }
        } else {
            $result = null;
        }

        return $result;
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
