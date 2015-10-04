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

use \Closure;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;

/**
 * @package Neomerx\JsonApi
 */
class RequestClosureAdapter implements CurrentRequestInterface
{
    /**
     * @var Closure
     */
    private $getContentClosure;

    /**
     * @var Closure
     */
    private $getParamsClosure;

    /**
     * @var Closure
     */
    private $getHeaderClosure;

    /**
     * Constructor.
     *
     * @param Closure $getContentClosure
     * @param Closure $getParamsClosure
     * @param Closure $getHeaderClosure
     */
    public function __construct(Closure $getContentClosure, Closure $getParamsClosure, Closure $getHeaderClosure)
    {
        $this->getParamsClosure  = $getParamsClosure;
        $this->getHeaderClosure  = $getHeaderClosure;
        $this->getContentClosure = $getContentClosure;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $closure = $this->getContentClosure;
        $result  = $closure();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getQueryParameters()
    {
        $closure = $this->getParamsClosure;
        $result  = $closure();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getHeader($name)
    {
        $closure = $this->getHeaderClosure;
        $result  = $closure($name);

        return $result;
    }
}
