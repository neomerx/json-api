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

use \Neomerx\JsonApi\I18n\Translator as T;
use \Neomerx\JsonApi\Exceptions\ErrorCollection;
use \Neomerx\JsonApi\Exceptions\JsonApiException as E;
use \Neomerx\JsonApi\Contracts\Http\Query\QueryCheckerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface as QP;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveQueryChecker implements QueryCheckerInterface
{
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
     * @param bool       $allowUnrecognized
     * @param array|null $includePaths
     * @param array|null $fieldSetTypes
     * @param array|null $sortParameters
     * @param array|null $pagingParameters
     * @param array|null $filteringParameters
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        $allowUnrecognized = true,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->includePaths        = $includePaths;
        $this->allowUnrecognized   = $allowUnrecognized;
        $this->fieldSetTypes       = $fieldSetTypes;
        $this->sortParameters      = $this->flip($sortParameters);
        $this->pagingParameters    = $this->flip($pagingParameters);
        $this->filteringParameters = $this->flip($filteringParameters);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function checkQuery(EncodingParametersInterface $parameters)
    {
        $errors = new ErrorCollection();

        $this->checkIncludePaths($errors, $parameters);
        $this->checkFieldSets($errors, $parameters);
        $this->checkFiltering($errors, $parameters);
        $this->checkSorting($errors, $parameters);
        $this->checkPaging($errors, $parameters);
        $this->checkUnrecognized($errors, $parameters);

        $errors->count() <= 0 ?: E::throwException(new E($errors, E::HTTP_CODE_BAD_REQUEST));
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkIncludePaths(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        $withinAllowed = $this->valuesWithinAllowed($parameters->getIncludePaths(), $this->includePaths);
        $withinAllowed === true ?: $errors->addQueryParameterError(QP::PARAM_INCLUDE, T::t(
            'Include paths should contain only allowed ones.'
        ));
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkFieldSets(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        $withinAllowed = $this->isFieldsAllowed($parameters->getFieldSets());
        $withinAllowed === true ?: $errors->addQueryParameterError(QP::PARAM_FIELDS, T::t(
            'Field sets should contain only allowed ones.'
        ));
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkFiltering(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getFilteringParameters(), $this->filteringParameters);
        $withinAllowed === true ?: $errors->addQueryParameterError(QP::PARAM_FILTER, T::t(
            'Filter should contain only allowed values.'
        ));
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkSorting(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        if ($parameters->getSortParameters() !== null && $this->sortParameters !== null) {
            foreach ($parameters->getSortParameters() as $sortParameter) {
                /** @var SortParameterInterface $sortParameter */
                if (array_key_exists($sortParameter->getField(), $this->sortParameters) === false) {
                    $errors->addQueryParameterError(QP::PARAM_SORT, T::t(
                        'Sort parameter %s is not allowed.',
                        [$sortParameter->getField()]
                    ));
                }
            }
        }
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function checkPaging(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        $withinAllowed = $this->keysWithinAllowed($parameters->getPaginationParameters(), $this->pagingParameters);
        $withinAllowed === true ?: $errors->addQueryParameterError(QP::PARAM_PAGE, T::t(
            'Page parameter should contain only allowed values.'
        ));
    }

    /**
     * @param ErrorCollection             $errors
     * @param EncodingParametersInterface $parameters
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function checkUnrecognized(ErrorCollection $errors, EncodingParametersInterface $parameters)
    {
        if ($this->allowUnrecognized === false && empty($parameters->getUnrecognizedParameters()) === false) {
            foreach ($parameters->getUnrecognizedParameters() as $name => $value) {
                $errors->addQueryParameterError($name, T::t('Parameter is not allowed.'));
            }
        }
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
