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
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParameterCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParameterChecker implements ParameterCheckerInterface
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
     * @var bool
     */
    private $allowExtensionsSupport;

    /**
     * @param ExceptionThrowerInterface $exceptionThrower
     * @param CodecMatcherInterface     $codecMatcher
     * @param bool                      $allowUnrecognized
     * @param array|null                $includePaths
     * @param array|null                $fieldSetTypes
     * @param array|null                $sortParameters
     * @param array|null                $pagingParameters
     * @param array|null                $filteringParameters
     * @param bool                      $allowExtSupport If JSON API extensions support is allowed.
     */
    public function __construct(
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        $allowExtSupport = false
    ) {
        $this->exceptionThrower       = $exceptionThrower;
        $this->codecMatcher           = $codecMatcher;
        $this->includePaths           = $includePaths;
        $this->allowUnrecognized      = $allowUnrecognized;
        $this->fieldSetTypes          = $fieldSetTypes;
        $this->sortParameters         = $this->flip($sortParameters);
        $this->pagingParameters       = $this->flip($pagingParameters);
        $this->filteringParameters    = $this->flip($filteringParameters);
        $this->allowExtensionsSupport = $allowExtSupport;
    }

    /**
     * @inheritdoc
     */
    public function check(ParametersInterface $parameters)
    {
        // Note: for the next 2 checks the order is specified by spec. See details inside.
        $this->checkAcceptHeader($parameters);
        $this->checkContentTypeHeader($parameters);

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
    protected function checkAcceptHeader(ParametersInterface $parameters)
    {
        $this->codecMatcher->matchEncoder($parameters->getAcceptHeader());

        // From spec: Servers MUST return a 406 Not Acceptable status code if
        // the application/vnd.api+json media type is modified by the ext parameter
        // in the Accept header of a request.

        // We return 406 if no match found for encoder or
        // if 'allowExtensionsSupport' set to false (and match found) we check 'ext' **parameter** to be not set.
        // Thus it can be configured whether we support extensions or not.

        $inputMediaType = $this->codecMatcher->getEncoderHeaderMatchedType();
        if ($this->isBadMediaType($inputMediaType)) {
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

        // From spec: servers MUST return a 415 Unsupported Media Type status code if
        // the application/vnd.api+json media type is modified by the ext parameter
        // in the Content-Type header of a request.

        // We return 415 if no match found for decoder or
        // if 'allowExtensionsSupport' set to false (and match found) we check 'ext' **parameter** to be not set.
        // Thus it can be configured whether we support extensions or not.

        $inputMediaType = $this->codecMatcher->getDecoderHeaderMatchedType();
        if ($this->isBadMediaType($inputMediaType)) {
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
     * @param MediaTypeInterface|null $mediaType
     *
     * @return bool
     */
    private function isBadMediaType(MediaTypeInterface $mediaType = null)
    {
        return $mediaType === null || (
            $this->allowExtensionsSupport === false &&
            $mediaType->getMediaType() === MediaTypeInterface::JSON_API_MEDIA_TYPE &&
            $mediaType->getParameters() !== null &&
            array_key_exists(MediaTypeInterface::PARAM_EXT, $mediaType->getParameters()) === true);
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
