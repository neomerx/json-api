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
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SortParameterInterface;

/**
 * @package Neomerx\JsonApi
 */
class Parameters extends EncodingParameters implements ParametersInterface
{
    /**
     * @var MediaTypeInterface
     */
    private $inputType;

    /**
     * @var MediaTypeInterface
     */
    private $outputType;

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
     * @param MediaTypeInterface            $inputType
     * @param MediaTypeInterface            $outputType
     * @param string[]|null                 $includePaths
     * @param array|null                    $fieldSets
     * @param SortParameterInterface[]|null $sortParameters
     * @param array|null                    $pagingParameters
     * @param array|null                    $filteringParameters
     * @param array|null                    $unrecognizedParams
     */
    public function __construct(
        MediaTypeInterface $inputType,
        MediaTypeInterface $outputType,
        $includePaths = null,
        array $fieldSets = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    ) {
        parent::__construct($includePaths, $fieldSets);

        $this->inputType           = $inputType;
        $this->outputType          = $outputType;
        $this->sortParameters      = $sortParameters;
        $this->pagingParameters    = $pagingParameters;
        $this->unrecognizedParams  = $unrecognizedParams;
        $this->filteringParameters = $filteringParameters;
    }

    /**
     * @inheritdoc
     */
    public function getInputMediaType()
    {
        return $this->inputType;
    }

    /**
     * @inheritdoc
     */
    public function getOutputMediaType()
    {
        return $this->outputType;
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
}
