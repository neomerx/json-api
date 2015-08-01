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
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Responses\Responses;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;
use \Neomerx\JsonApi\Contracts\Integration\NativeResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

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
     * @var ParametersFactoryInterface
     */
    private $factory;

    /**
     * @param ParametersFactoryInterface $factory
     * @param NativeResponsesInterface   $responses
     * @param Closure                    $extensionsClosure Returns extensions for the current request/controller.
     * @param int                        $defaultStatusCode Default status code for unknown exceptions.
     */
    public function __construct(
        ParametersFactoryInterface $factory,
        NativeResponsesInterface $responses,
        Closure $extensionsClosure,
        $defaultStatusCode
    ) {
        assert('is_int($defaultStatusCode) && $defaultStatusCode >= 500 && $defaultStatusCode < 600');

        $this->factory           = $factory;
        $this->responses         = new Responses($responses);
        $this->extensionsClosure = $extensionsClosure;
        $this->defaultStatusCode = $defaultStatusCode;
    }

    /**
     * @inheritdoc
     */
    public function registerRender($exceptionClass, Closure $render)
    {
        $this->renders[$exceptionClass] = $render;
    }

    /**
     * @inheritdoc
     */
    public function registerHttpCodeMapping(array $exceptionMapping)
    {
        foreach ($exceptionMapping as $exceptionClass => $httpStatusCode) {
            $this->registerRender($exceptionClass, $this->getHttpCodeRender($httpStatusCode));
        }
    }

    /**
     * @inheritdoc
     */
    public function registerJsonApiErrorMapping(array $exceptionMapping)
    {
        foreach ($exceptionMapping as $exceptionClass => $httpStatusCode) {
            $this->registerRender($exceptionClass, $this->getErrorsRender($httpStatusCode));
        }
    }

    /**
     * @inheritdoc
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
        /**
         * @param array $headers
         *
         * @return mixed
         */
        return function (array $headers = []) {
            $render = $this->getHttpCodeRender($this->defaultStatusCode);
            return $render($headers);
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
        /**
         * @param array $headers
         *
         * @return mixed
         */
        return function (array $headers = []) use ($statusCode) {
            $extensionsClosure   = $this->extensionsClosure;
            /** @var SupportedExtensionsInterface $supportedExtensions */
            $supportedExtensions = $extensionsClosure();

            $content   = null;
            $mediaType = $this->factory->createMediaType(
                MediaTypeInterface::JSON_API_TYPE,
                MediaTypeInterface::JSON_API_SUB_TYPE
            );

            return $this->responses->getResponse($statusCode, $mediaType, $content, $supportedExtensions, $headers);
        };
    }

    /**
     * Get render that returns JSON API response with JSON API Error objects and specified HTTP status code.
     *
     * @param int $statusCode
     *
     * @return Closure
     */
    protected function getErrorsRender($statusCode)
    {
        /**
         * @param ErrorInterface[] $errors
         * @param EncoderOptions   $encodeOptions
         * @param array            $headers
         *
         * @return mixed
         */
        return function (array $errors, EncoderOptions $encodeOptions = null, array $headers = []) use ($statusCode) {
            $extensionsClosure   = $this->extensionsClosure;
            /** @var SupportedExtensionsInterface $supportedExtensions */
            $supportedExtensions = $extensionsClosure();

            $content   = Encoder::instance([], $encodeOptions)->errors($errors);
            $mediaType = $this->factory->createMediaType(
                MediaTypeInterface::JSON_API_TYPE,
                MediaTypeInterface::JSON_API_SUB_TYPE
            );

            return $this->responses->getResponse($statusCode, $mediaType, $content, $supportedExtensions, $headers);
        };
    }
}
