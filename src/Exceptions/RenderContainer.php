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
use Neomerx\JsonApi\Contracts\Exceptions\Renderer\ExceptionRendererInterface;
use Neomerx\JsonApi\Exceptions\Renderer\ErrorsRenderer;
use Neomerx\JsonApi\Exceptions\Renderer\HttpCodeRenderer;
use \Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RenderContainer implements RenderContainerInterface
{
    /**
     * @var array
     */
    private $renderers = [];

    /**
     * @var ResponsesInterface
     */
    private $responses;

    /**
     * @var Closure
     */
    private $extensionsClosure;

    /**
     * @var int
     */
    private $defaultStatusCode;

    /**
     * @param ResponsesInterface         $responses
     * @param Closure                    $extensionsClosure Returns extensions for the current request/controller.
     * @param int                        $defaultStatusCode Default status code for unknown exceptions.
     */
    public function __construct(
        ResponsesInterface $responses,
        Closure $extensionsClosure,
        $defaultStatusCode
    ) {
        assert('is_int($defaultStatusCode) && $defaultStatusCode >= 500 && $defaultStatusCode < 600');

        $this->responses         = $responses;
        $this->extensionsClosure = $extensionsClosure;
        $this->defaultStatusCode = $defaultStatusCode;
    }

    /**
     * @inheritdoc
     */
    public function registerRenderer($exceptionClass, ExceptionRendererInterface $render)
    {
        $this->renderers[$exceptionClass] = $render;
    }

    /**
     * @inheritdoc
     */
    public function registerHttpCodeMapping(array $exceptionMapping)
    {
        foreach ($exceptionMapping as $exceptionClass => $httpStatusCode) {
            $this->registerRenderer($exceptionClass, $this->getHttpCodeRenderer($httpStatusCode));
        }
    }

    /**
     * @inheritdoc
     */
    public function registerJsonApiErrorMapping(array $exceptionMapping)
    {
        foreach ($exceptionMapping as $exceptionClass => $httpStatusCode) {
            $this->registerRenderer($exceptionClass, $this->getErrorsRenderer($httpStatusCode));
        }
    }

    /**
     * @inheritdoc
     */
    public function getRenderer(Exception $exception)
    {
        $exClass  = get_class($exception);
        $renderer = isset($this->renderers[$exClass]) === true ? $this->renderers[$exClass] : $this->getDefaultRenderer();

        if ($this->extensionsClosure instanceof \Closure) {
            $renderer->withSupportedExtensions(call_user_func($this->extensionsClosure));
        }

        return $renderer;
    }

    /**
     * Get default render for unregistered exception. You can override this
     * method to change unhandled exception representation.
     *
     * @return HttpCodeRenderer
     */
    protected function getDefaultRenderer()
    {
        return $this->getHttpCodeRenderer($this->defaultStatusCode);
    }

    /**
     * Get render that returns JSON API response with empty body and specified HTTP status code.
     *
     * @param int $statusCode
     * @return HttpCodeRenderer
     */
    protected function getHttpCodeRenderer($statusCode)
    {
        $renderer = new HttpCodeRenderer($this->responses);
        $renderer->withStatusCode($statusCode);
        return $renderer;
    }

    /**
     * Get render that returns JSON API response with JSON API Error objects and specified HTTP status code.
     *
     * @param int $statusCode
     * @return ErrorsRenderer
     */
    protected function getErrorsRenderer($statusCode)
    {
        $renderer = new ErrorsRenderer($this->responses);
        $renderer->withStatusCode($statusCode);
        return $renderer;
    }
}
