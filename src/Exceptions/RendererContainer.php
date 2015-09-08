<?php namespace Neomerx\JsonApi\Exceptions;

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

use \Neomerx\JsonApi\Contracts\Exceptions\RendererInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RendererContainerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RendererContainer implements RendererContainerInterface
{
    /**
     * @var array
     */
    private $renderers = [];

    /**
     * @var RendererInterface
     */
    private $defaultRenderer;

    /**
     * @param RendererInterface  $defaultRenderer
     */
    public function __construct(RendererInterface $defaultRenderer)
    {
        $this->defaultRenderer = $defaultRenderer;
    }

    /**
     * @inheritdoc
     */
    public function registerRenderer($exceptionClass, RendererInterface $renderer)
    {
        $this->renderers[$exceptionClass] = $renderer;
    }

    /**
     * @inheritdoc
     */
    public function getRenderer($exceptionClass)
    {
        $hasRenderer = isset($this->renderers[$exceptionClass]) === true;
        $renderer    = $hasRenderer ? $this->renderers[$exceptionClass] : $this->defaultRenderer;

        return $renderer;
    }
}
