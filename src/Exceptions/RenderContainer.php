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

use \Closure;
use \Exception;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RenderContainer implements RenderContainerInterface
{
    /**
     * @var array
     */
    private $renders = [];

    /**
     * @var Closure
     */
    private $codeResponseClosure;

    /**
     * @var int
     */
    private $defaultStatusCode;

    /**
     * @param Closure $codeResponse      Closure accept $statusCode and returns Response.
     * @param int     $defaultStatusCode Default status code for unknown exceptions.
     */
    public function __construct(Closure $codeResponse, $defaultStatusCode)
    {
        assert('is_int($defaultStatusCode) && $defaultStatusCode >= 500 && $defaultStatusCode < 600');

        $this->codeResponseClosure = $codeResponse;
        $this->defaultStatusCode   = $defaultStatusCode;
    }

    /**
     * @inheritdoc
     */
    public function registerRender($exceptionClass, Closure $render)
    {
        $this->renders[$exceptionClass] = $render;
    }

    /**
     * Register HTTP status code mapping for exceptions.
     *
     * @param array $exceptionMapping
     *
     * @return void
     */
    public function registerMapping(array $exceptionMapping)
    {
        foreach ($exceptionMapping as $exceptionClass => $httpStatusCode) {
            $this->registerRender($exceptionClass, $this->getHttpCodeRender($httpStatusCode));
        }
    }

    /**
     * Get registered or default render for exception.
     *
     * @param Exception $exception
     *
     * @return Closure
     */
    public function getRender(Exception $exception)
    {
        $exClass = get_class($exception);
        return isset($this->renders[$exClass]) === true ? $this->renders[$exClass] : $this->getDefaultRender();
    }

    /**
     * Get default render for unregistered exception. You can override this
     * method to change unhandled exception representation.
     *
     * @return Closure
     */
    protected function getDefaultRender()
    {
        return function () {
            $render = $this->getHttpCodeRender($this->defaultStatusCode);
            return $render();
        };
    }

    /**
     * Get render that returns JSON API response with empty body and specified HTTP status code.
     *
     * @param int $statusCode
     *
     * @return Closure
     */
    protected function getHttpCodeRender($statusCode)
    {
        return function () use ($statusCode) {
            $codeResponseClosure = $this->codeResponseClosure;
            return $codeResponseClosure($statusCode);
        };
    }
}
