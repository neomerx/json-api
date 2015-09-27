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

use \Exception;
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Exceptions\RendererInterface;
use \Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class BaseRenderer implements RendererInterface
{
    /**
     * @param Exception $exception
     *
     * @return mixed
     */
    abstract public function getContent(Exception $exception);

    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers = [];

    /**
     * @var SupportedExtensionsInterface|null
     */
    private $extensions;

    /**
     * @var MediaTypeInterface|null
     */
    private $mediaType;

    /**
     * @var ResponsesInterface
     */
    private $responses;

    /**
     * @param ResponsesInterface $responses
     */
    public function __construct(ResponsesInterface $responses)
    {
        $this->responses = $responses;
    }

    /**
     * @inheritdoc
     */
    public function withStatusCode($statusCode)
    {
        $this->statusCode = (int)$statusCode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withSupportedExtensions(SupportedExtensionsInterface $extensions)
    {
        $this->extensions = $extensions;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withMediaType(MediaTypeInterface $mediaType)
    {
        $this->mediaType = $mediaType;

        return $this;
    }

    /**
     * @return int|null
     */
    protected function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    protected function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return SupportedExtensionsInterface|null
     */
    protected function getSupportedExtensions()
    {
        return $this->extensions;
    }

    /**
     * @return MediaTypeInterface|null
     */
    protected function getMediaType()
    {
        return $this->mediaType;
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $exception)
    {
        $mediaType = $this->getMediaType();

        // Media type should be specified for exception renderers
        $mediaType !== null ?: Exceptions::throwInvalidArgument('mediaType');

        return $this->responses->getResponse(
            $this->getStatusCode(),
            $mediaType,
            $this->getContent($exception),
            $this->getSupportedExtensions(),
            $this->getHeaders()
        );
    }
}
