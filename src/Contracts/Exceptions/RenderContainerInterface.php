<?php namespace Neomerx\JsonApi\Contracts\Exceptions;

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

use \Exception;
use Neomerx\JsonApi\Contracts\Exceptions\Renderer\ExceptionRendererInterface;

/**
 * @package Neomerx\JsonApi
 */
interface RenderContainerInterface
{
    /**
     * Register exception render
     *
     * @param string  $exceptionClass
     * @param ExceptionRendererInterface $render
     * @return void
     */
    public function registerRenderer($exceptionClass, ExceptionRendererInterface $render);

    /**
     * Register HTTP status code mapping for exceptions.
     *
     * @param array $exceptionMapping
     *
     * @return void
     */
    public function registerHttpCodeMapping(array $exceptionMapping);

    /**
     * Register JSON API Error object renders mapping for exceptions.
     *
     * @param array $exceptionMapping
     *
     * @return void
     */
    public function registerJsonApiErrorMapping(array $exceptionMapping);

    /**
     * Get registered or default render for exception.
     *
     * @param Exception $exception
     * @return ExceptionRendererInterface
     */
    public function getRenderer(Exception $exception);
}
