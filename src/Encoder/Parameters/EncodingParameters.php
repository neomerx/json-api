<?php namespace Neomerx\JsonApi\Encoder\Parameters;

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

use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
class EncodingParameters implements EncodingParametersInterface
{
    /**
     * @var array|null
     */
    private $includePaths;

    /**
     * @var array|null
     */
    private $fieldSets;

    /**
     * @var SortParameterInterface[]|null
     */
    private $sortParameters;

    /**
     * @var array|null
     */
    private $pagingParameters;

    /**
     * @var array|null
     */
    private $filteringParameters;

    /**
     * @var array|null
     */
    private $unrecognizedParams;

    /**
     * @param string[]|null                 $includePaths
     * @param array|null                    $fieldSets
     * @param SortParameterInterface[]|null $sortParameters
     * @param array|null                    $pagingParameters
     * @param array|null                    $filteringParameters
     * @param array|null                    $unrecognizedParams
     */
    public function __construct(
        $includePaths = null,
        array $fieldSets = null,
        $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    ) {
        $this->fieldSets           = $fieldSets;
        $this->includePaths        = $includePaths;
        $this->sortParameters      = $sortParameters;
        $this->pagingParameters    = $pagingParameters;
        $this->unrecognizedParams  = $unrecognizedParams;
        $this->filteringParameters = $filteringParameters;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * @inheritdoc
     */
    public function getFieldSets()
    {
        return $this->fieldSets;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getFieldSet($type)
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        return (isset($this->fieldSets[$type]) === true ? $this->fieldSets[$type] : null);
    }

    /**
     * @inheritdoc
     */
    public function getSortParameters()
    {
        return $this->sortParameters;
    }

    /**
     * @inheritdoc
     */
    public function getPaginationParameters()
    {
        return $this->pagingParameters;
    }

    /**
     * @inheritdoc
     */
    public function getFilteringParameters()
    {
        return $this->filteringParameters;
    }

    /**
     * @inheritdoc
     */
    public function getUnrecognizedParameters()
    {
        return $this->unrecognizedParams;
    }

    /**
     * @inheritdoc
     */
    public function isEmpty()
    {
        return
            empty($this->getFieldSets()) === true && empty($this->getIncludePaths()) === true &&
            empty($this->getSortParameters()) === true && empty($this->getPaginationParameters()) === true &&
            empty($this->getFilteringParameters()) === true;
    }
}
