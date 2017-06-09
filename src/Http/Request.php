<?php namespace Neomerx\JsonApi\Http;

/**
 * Copyright 2015-2017 info@neomerx.com
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
use \LogicException;
use \Psr\Http\Message\UriInterface;
use \Psr\Http\Message\StreamInterface;
use \Psr\Http\Message\ServerRequestInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Request implements ServerRequestInterface
{
    /**
     * @var Closure
     */
    private $getMethodClosure;

    /**
     * @var Closure
     */
    private $getHeaderClosure;

    /**
     * @var Closure
     */
    private $getQueryParamsClosure;

    /**
     * @param Closure $getMethodClosure
     * @param Closure $getHeaderClosure
     * @param Closure $getQueryParamsClosure
     *
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Closure $getMethodClosure,
        Closure $getHeaderClosure,
        Closure $getQueryParamsClosure
    ) {
        $this->getMethodClosure      = $getMethodClosure;
        $this->getHeaderClosure      = $getHeaderClosure;
        $this->getQueryParamsClosure = $getQueryParamsClosure;
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

    /**
     * @inheritdoc
     */
    public function getQueryParams()
    {
        $closure = $this->getQueryParamsClosure;
        $result  = $closure();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        $closure = $this->getMethodClosure;
        $result  = $closure();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getParsedBody()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getProtocolVersion()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withProtocolVersion($version)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getHeaders()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function hasHeader($name)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getHeaderLine($name)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withHeader($name, $value)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withAddedHeader($name, $value)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withoutHeader($name)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withBody(StreamInterface $body)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getRequestTarget()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withRequestTarget($requestTarget)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withMethod($method)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getUri()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getServerParams()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getCookieParams()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withCookieParams(array $cookies)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withQueryParams(array $query)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getUploadedFiles()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withParsedBody($data)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name, $default = null)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withAttribute($name, $value)
    {
        // Method is not used.
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function withoutAttribute($name)
    {
        // Method is not used.
        throw new LogicException();
    }
}
