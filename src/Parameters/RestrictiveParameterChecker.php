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

use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Parameters\CombinedCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParameterChecker implements CombinedCheckerInterface
{
    /**
     * @var ExceptionThrowerInterface
     */
    private $exceptionThrower;

    /**
     * @var CodecMatcherInterface
     */
    private $codecMatcher;

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
     * @param ExceptionThrowerInterface $exceptionThrower
     * @param CodecMatcherInterface     $codecMatcher
     * @param bool                      $allowUnrecognized
     * @param array|null                $includePaths
     * @param array|null                $fieldSetTypes
     * @param array|null                $sortParameters
     * @param array|null                $pagingParameters
     * @param array|null                $filteringParameters
     */
    public function __construct(
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->exceptionThrower    = $exceptionThrower;
        $this->codecMatcher        = $codecMatcher;
        $this->includePaths        = $includePaths;
        $this->allowUnrecognized   = $allowUnrecognized;
        $this->fieldSetTypes       = $fieldSetTypes;
        $this->sortParameters      = $this->flip($sortParameters);
        $this->pagingParameters    = $this->flip($pagingParameters);
        $this->filteringParameters = $this->flip($filteringParameters);
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function check(ParametersInterface $parameters)
    {
        $this->checkHeaders($parameters);
        $this->checkParameters($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkParameters(ParametersInterface $parameters)
    {
        $this->checkIncludePaths($parameters);
        $this->checkFieldSets($parameters);
        $this->checkFiltering($parameters);
        $this->checkSorting($parameters);
        $this->checkPaging($parameters);
        $this->checkUnrecognized($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        // Note: for these checks the order is specified by spec. See details inside.
        $this->checkAcceptHeader($parameters);
        $this->checkContentTypeHeader($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkAcceptHeader(ParametersInterface $parameters)
    {
        $this->codecMatcher->matchEncoder($parameters->getAcceptHeader());

        // From spec: Servers MUST respond with a 406 Not Acceptable status code
        // if a request's Accept header contains the JSON API media type and all
        // instances of that media type are modified with media type parameters.

        // We return 406 if no match found for encoder (media type with or wo parameters)
        // If no encoders were configured for media types with parameters we return 406 anyway
        if ($this->codecMatcher->getEncoderHeaderMatchedType() === null) {
            $this->exceptionThrower->throwNotAcceptable();
        }
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkContentTypeHeader(ParametersInterface $parameters)
    {
        // Do not allow specify more than 1 media type for input data. Otherwise which one is correct?
        if (count($parameters->getContentTypeHeader()->getMediaTypes()) > 1) {
            $this->exceptionThrower->throwBadRequest();
        }

        $this->codecMatcher->findDecoder($parameters->getContentTypeHeader());

        // From spec: Servers MUST respond with a 415 Unsupported Media Type status code
        // if a request specifies the header Content-Type: application/vnd.api+json with
        // any media type parameters.

        // We return 415 if no match found for decoder (media type with or wo parameters)
        // If no decoders were configured for media types with parameters we return 415 anyway
        if ($this->codecMatcher->getDecoderHeaderMatchedType() === null) {
            $this->exceptionThrower->throwUnsupportedMediaType();
        }
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkIncludePaths(ParametersInterface $parameters)
    {
        $withinAllowed = $this->valuesWithinAllowed($parameters->getIncludePaths(), $this->includePaths);
        $withinAllowed === true ?: $this->exceptionThrower->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkFieldSets(ParametersInterface $parameters)
    {
        $withinAllowed = $this->isFieldsAllowed($parameters->getFieldSets());
        $withinAllowed === true ?: $this->exceptionThrower->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkFiltering(ParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getFilteringParameters(), $this->filteringParameters);
        $withinAllowed === true ?: $this->exceptionThrower->throwBadRequest();
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
                    $this->exceptionThrower->throwBadRequest();
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
        $withinAllowed === true ?: $this->exceptionThrower->throwBadRequest();
    }

    /**
     * @param ParametersInterface $parameters
     */
    protected function checkUnrecognized(ParametersInterface $parameters)
    {
        $this->allowUnrecognized === true || empty($parameters->getUnrecognizedParameters()) === true ?:
            $this->exceptionThrower->throwBadRequest();
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

    /**
     * @param array|null $array
     *
     * @return array|null
     */
    private function flip(array $array = null)
    {
        return $array === null ? null : array_flip($array);
    }

    /**
     * Check input fields against allowed.
     *
     * @param array|null $fields
     *
     * @return bool
     */
    private function isFieldsAllowed(array $fields = null)
    {
        if ($this->fieldSetTypes === null || $fields === null) {
            return true;
        }

        foreach ($fields as $type => $requestedFields) {
            if (array_key_exists($type, $this->fieldSetTypes) === false) {
                return false;
            }

            $allowedFields = $this->fieldSetTypes[$type];

            // if not all fields are allowed and requested more fields than allowed
            if ($allowedFields !== null && empty(array_diff($requestedFields, $allowedFields)) === false) {
                return false;
            }
        }

        return true;
    }
}
