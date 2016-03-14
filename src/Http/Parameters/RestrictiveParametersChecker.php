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

use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\QueryCheckerInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\HeadersCheckerInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersCheckerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParametersChecker implements ParametersCheckerInterface
{
    /**
     * @var HeadersCheckerInterface
     */
    private $headerChecker;

    /**
     * @var QueryCheckerInterface
     */
    private $queryChecker;

    /**
     * @param HeadersCheckerInterface   $headersChecker
     * @param QueryCheckerInterface     $queryChecker
     */
    public function __construct(HeadersCheckerInterface $headersChecker, QueryCheckerInterface $queryChecker)
    {
        $this->headerChecker = $headersChecker;
        $this->queryChecker  = $queryChecker;
    }

    /**
     * @inheritdoc
     */
    public function check(ParametersInterface $parameters)
    {
        $this->checkHeaders($parameters);
        $this->checkQuery($parameters);
    }

    /**
     * @inheritdoc
     */
    public function checkQuery(ParametersInterface $parameters)
    {
        $this->queryChecker->checkQuery($parameters);
    }

    /**
     * @inheritdoc
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        $this->headerChecker->checkHeaders($parameters);
    }
}
