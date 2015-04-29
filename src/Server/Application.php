<?php namespace Neomerx\JsonApi\Server;

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
use \Neomerx\JsonApi\Exceptions\JsonApiException;
use \Neomerx\JsonApi\Contracts\Server\RequestInterface;
use \Neomerx\JsonApi\Contracts\Server\ResponseInterface;
use \Neomerx\JsonApi\Contracts\Server\RequestHandlerInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiNotAcceptableException;
use \Neomerx\JsonApi\Contracts\Conversion\MediaConverterInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiUnsupportedMediaTypeException;
use \Neomerx\JsonApi\Contracts\Server\ExtensionRequestHandlerInterface;

/**
 * @package Neomerx\JsonApi
 */
class Application implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerInterface
     */
    protected $jsonApiHandler;

    /**
     * @var MediaConverterInterface
     */
    protected $jsonApiConverter;

    /**
     * @var MediaConverterInterface[]
     */
    protected $converters;

    /**
     * @var ExtensionRequestHandlerInterface[]
     */
    protected $extensions;

    /**
     * @var Closure
     */
    protected $exceptionClosure;

    /**
     * @param RequestHandlerInterface            $jsonApiHandler
     * @param MediaConverterInterface            $jsonApiConverter
     * @param Closure                            $exceptionClosure
     * @param MediaConverterInterface[]|null          $converters
     * @param ExtensionRequestHandlerInterface[]|null $extensions
     */
    public function __construct(
        RequestHandlerInterface $jsonApiHandler,
        MediaConverterInterface $jsonApiConverter,
        Closure $exceptionClosure,
        array $converters = null,
        array $extensions = null
    ) {
        assert('empty($converters) === true || isset($converters[RequestInterface::JSON_API_CONTENT_TYPE]) === false');
        assert('empty($extensions) === true || isset($extensions[RequestInterface::JSON_API_CONTENT_TYPE]) === false');

        $this->converters       = $converters;
        $this->extensions       = $extensions;
        $this->jsonApiHandler   = $jsonApiHandler;
        $this->exceptionClosure = $exceptionClosure;
        $this->jsonApiConverter = $jsonApiConverter;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request)
    {
        try {
            // check if we support for requested media type and capabilities for provided data
            $mediaType = $request->getMediaType();
            $converter = $this->getMediaConverter($mediaType);
            $converter !== null ?: $this->throwEx(new JsonApiNotAcceptableException());

            $capabilityName = $request->getCapability();
            $handler        = $this->getHandler($capabilityName);
            $handler !== null ?: $this->throwEx(new JsonApiUnsupportedMediaTypeException());

            $response  = $converter->convert(
                $handler->handle($request)
            );

        } catch (JsonApiException $exception) {
            $handler  = $this->exceptionClosure;
            $response = ($handler !== null ? $handler($exception) : $this->throwEx($exception));
        }

        return $response;
    }

    /**
     * @param string $capabilityName
     *
     * @return RequestHandlerInterface|null
     */
    protected function getHandler($capabilityName)
    {
        return $capabilityName === RequestInterface::JSON_API_CONTENT_TYPE ? $this->jsonApiHandler :
            (isset($this->extensions[$capabilityName]) === true ? $this->extensions[$capabilityName] : null);
    }

    /**
     * @param string $mediaType
     *
     * @return MediaConverterInterface|null
     */
    protected function getMediaConverter($mediaType)
    {
        return $mediaType === RequestInterface::JSON_API_CONTENT_TYPE ? $this->jsonApiConverter :
            (isset($this->converters[$mediaType]) == true ? $this->converters[$mediaType] : null);
    }

    /**
     * @param JsonApiException $exception
     *
     * @return mixed
     *
     * @throws JsonApiException
     */
    protected function throwEx(JsonApiException $exception)
    {
        throw $exception;
    }
}
