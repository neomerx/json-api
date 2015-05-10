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

use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionsInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParameterCheckerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParameterChecker implements ParameterCheckerInterface
{
    /**
     * @var ExceptionsInterface
     */
    private $exceptions;

    /**
     * @var array
     */
    private $inputMediaTypes;

    /**
     * @var array
     */
    private $outputMediaTypes;

    /**
     * @var bool
     */
    private $allowUnrecognized;

    /**
     * @var array|null
     */
    private $includePaths;

    /**
     * @var array|null
     */
    private $fieldSetTypes;

    /**
     * @var array|null
     */
    private $pagingParameters;

    /**
     * @var array|null
     */
    private $sortParameters;

    /**
     * @var array|null
     */
    private $filteringParameters;

    /**
     * @param ExceptionsInterface $exceptions
     * @param array               $inputMediaTypes
     * @param array               $outputMediaTypes
     * @param bool                $allowUnrecognized
     * @param array|null          $includePaths
     * @param array|null          $fieldSetTypes
     * @param array|null          $sortParameters
     * @param array|null          $pagingParameters
     * @param array|null          $filteringParameters
     */
    public function __construct(
        ExceptionsInterface $exceptions,
        array $inputMediaTypes,
        array $outputMediaTypes,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->exceptions          = $exceptions;
        $this->inputMediaTypes     = $inputMediaTypes;
        $this->outputMediaTypes    = $outputMediaTypes;
        $this->includePaths        = $includePaths;
        $this->allowUnrecognized   = $allowUnrecognized;
        $this->fieldSetTypes       = ($fieldSetTypes === null ? null : array_flip($fieldSetTypes));
        $this->sortParameters      = ($sortParameters === null ? null : array_flip($sortParameters));
        $this->pagingParameters    = ($pagingParameters === null ? null : array_flip($pagingParameters));
        $this->filteringParameters = ($filteringParameters === null ? null : array_flip($filteringParameters));
    }

    /**
     * @inheritdoc
     */
    public function check(ParametersInterface $parameters)
    {
        // Note: for the next 2 checks the order is specified by spec. See details inside.
        $this->checkOutputMediaType($parameters);
        $this->checkInputMediaType($parameters);

        $this->checkIncludePaths($parameters);
        $this->checkFieldSets($parameters);
        $this->checkFiltering($parameters);
        $this->checkSorting($parameters);
        $this->checkPaging($parameters);
        $this->checkUnrecognized($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkOutputMediaType(ParametersInterface $parameters)
    {
        $mediaType  = $parameters->getOutputMediaType()->getMediaType();
        $extensions = $parameters->getOutputMediaType()->getExtensions();

        // Clients MAY request a particular media type extension by including its name in the ext media type parameter
        // with the Accept header. Servers that do not support a requested extension or combination of extensions MUST
        // return a 406 Not Acceptable status code.
        // For example, application/vnd.api+json; ext="ext1,ext2"
        if ($mediaType === null ||
            $this->isMediaTypeSupported($this->outputMediaTypes, $mediaType, $extensions) === false
        ) {
            $this->exceptions->throwNotAcceptable();
        }
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkInputMediaType(ParametersInterface $parameters)
    {
        $mediaType  = $parameters->getInputMediaType()->getMediaType();
        $extensions = $parameters->getInputMediaType()->getExtensions();

        // If the media type in the Accept header is supported by a server but the media type in the
        // Content-Type header is unsupported, the server MUST return a 415 Unsupported Media Type status code.
        if ($mediaType === null ||
            $this->isMediaTypeSupported($this->inputMediaTypes, $mediaType, $extensions) === false
        ) {
            $this->exceptions->throwUnsupportedMediaType();
        }
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkIncludePaths(ParametersInterface $parameters)
    {
        $withinAllowed = $this->valuesWithinAllowed($parameters->getIncludePaths(), $this->includePaths);
        $withinAllowed === true ?: $this->exceptions->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkFieldSets(ParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getFieldSets(), $this->fieldSetTypes);
        $withinAllowed === true ?: $this->exceptions->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkFiltering(ParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getFilteringParameters(), $this->filteringParameters);
        $withinAllowed === true ?: $this->exceptions->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkSorting(ParametersInterface $parameters)
    {
        if ($parameters->getSortParameters() !== null && $this->sortParameters !== null) {
            foreach ($parameters->getSortParameters() as $sortParameter) {
                /** @var SortParameterInterface $sortParameter */
                if (array_key_exists($sortParameter->getField(), $this->sortParameters) === false) {
                    $this->exceptions->throwBadRequest();
                }
            }
        }
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkPaging(ParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getPaginationParameters(), $this->pagingParameters);
        $withinAllowed === true ?: $this->exceptions->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkUnrecognized(ParametersInterface $parameters)
    {
        $this->allowUnrecognized === true || empty($parameters->getUnrecognizedParameters()) === true ?:
            $this->exceptions->throwBadRequest();
    }

    /**
     * @param array       $types
     * @param string      $mediaType
     * @param string|null $extensions
     *
     * @return bool
     */
    private function isMediaTypeSupported(array $types, $mediaType, $extensions = null)
    {
        return ($extensions === null ? isset($types[$mediaType]) : isset($types[$mediaType][$extensions]));
    }

    /**
     * @param array|null $toCheck
     * @param array|null $allowed
     *
     * @return bool
     */
    private function keysWithinAllowed(array $toCheck = null, array $allowed = null)
    {
        return $toCheck === null || $allowed === null || empty(array_diff_key($toCheck, $allowed));
    }

    /**
     * @param array|null $toCheck
     * @param array|null $allowed
     *
     * @return bool
     */
    private function valuesWithinAllowed(array $toCheck = null, array $allowed = null)
    {
        return $toCheck === null || $allowed === null || empty(array_diff($toCheck, $allowed));
    }
}
