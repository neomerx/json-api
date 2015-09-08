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

use Neomerx\JsonApi\Contracts\Parameters\ParameterCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;
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
     * @param bool                      $allowUnrecognized
     * @param array|null                $includePaths
     * @param array|null                $fieldSetTypes
     * @param array|null                $sortParameters
     * @param array|null                $pagingParameters
     * @param array|null                $filteringParameters
     */
    public function __construct(
        ExceptionThrowerInterface $exceptionThrower,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->exceptionThrower    = $exceptionThrower;
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
