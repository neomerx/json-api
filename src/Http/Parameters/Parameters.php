<?php namespace Neomerx\JsonApi\Http\Parameters;

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

use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\SortParameterInterface;

/**
 * @package Neomerx\JsonApi
 */
class Parameters extends EncodingParameters implements ParametersInterface
{
    /**
     * @var HeaderInterface
     */
    private $contentType;

    /**
     * @var AcceptHeaderInterface
     */
    private $accept;

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
     * @param HeaderInterface               $contentType
     * @param AcceptHeaderInterface         $accept
     * @param string[]|null                 $includePaths
     * @param array|null                    $fieldSets
     * @param SortParameterInterface[]|null $sortParameters
     * @param array|null                    $pagingParameters
     * @param array|null                    $filteringParameters
     * @param array|null                    $unrecognizedParams
     */
    public function __construct(
        HeaderInterface $contentType,
        AcceptHeaderInterface $accept,
        $includePaths = null,
        array $fieldSets = null,
        $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    ) {
        parent::__construct($includePaths, $fieldSets);

        $this->contentType         = $contentType;
        $this->accept              = $accept;
        $this->sortParameters      = $sortParameters;
        $this->pagingParameters    = $pagingParameters;
        $this->unrecognizedParams  = $unrecognizedParams;
        $this->filteringParameters = $filteringParameters;
    }

    /**
     * @inheritdoc
     */
    public function getContentTypeHeader()
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function getAcceptHeader()
    {
        return $this->accept;
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
