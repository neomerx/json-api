<?php namespace Neomerx\JsonApi\Http\Headers;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\HttpFactoryInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException as E;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use function Neomerx\JsonApi\I18n\translate as _;

/**
 * @package Neomerx\JsonApi
 */
class HeaderParametersParser implements HeaderParametersParserInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * Message code.
     */
    const MSG_INVALID_HEADER = 0;

    /**
     * Default messages.
     */
    const MESSAGES = [
        self::MSG_INVALID_HEADER => 'Type must be non-empty string.',
    ];

    /**
     * @var HttpFactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $messages;

    /**
     * @param HttpFactoryInterface $factory
     * @param array                $messages
     */
    public function __construct(HttpFactoryInterface $factory, $messages = self::MESSAGES)
    {
        $this->factory  = $factory;
        $this->messages = $messages;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function parse(ServerRequestInterface $request, bool $checkContentType = true): HeaderParametersInterface
    {
        $acceptHeader      = null;
        $contentTypeHeader = null;

        if ($checkContentType === true) {
            try {
                $header            = $this->getHeader($request, HeaderInterface::HEADER_CONTENT_TYPE);
                $contentTypeHeader = Header::parse($header, HeaderInterface::HEADER_CONTENT_TYPE);
            } catch (InvalidArgumentException $exception) {
                $title = _($this->messages[self::MSG_INVALID_HEADER], HeaderInterface::HEADER_CONTENT_TYPE);
                $error = new Error(null, null, null, null, $title);
                E::throwException(new E([$error], E::HTTP_CODE_BAD_REQUEST, $exception));
            }
        }

        try {
            $header       = $this->getHeader($request, HeaderInterface::HEADER_ACCEPT);
            $acceptHeader = AcceptHeader::parse($header);
        } catch (InvalidArgumentException $exception) {
            $title = _($this->messages[self::MSG_INVALID_HEADER], HeaderInterface::HEADER_ACCEPT);
            $error = new Error(null, null, null, null, $title);
            E::throwException(new E([$error], E::HTTP_CODE_BAD_REQUEST, $exception));
        }

        $method = $request->getMethod();

        return $checkContentType === true ?
            $this->factory->createHeaderParameters($method, $acceptHeader, $contentTypeHeader) :
            $this->factory->createNoContentHeaderParameters($method, $acceptHeader);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $name
     *
     * @return string
     */
    private function getHeader(ServerRequestInterface $request, string $name): string
    {
        $value = $request->getHeader($name);
        if (empty($value) === false) {
            $value = $value[0];
            if (empty($value) === false) {
                return $value;
            }
        }

        return MediaTypeInterface::JSON_API_MEDIA_TYPE;
    }
}
