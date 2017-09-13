<?php namespace Neomerx\JsonApi\Http\Headers;

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

use \InvalidArgumentException;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\I18n\Translator as T;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiException as E;
use \Neomerx\JsonApi\Contracts\Http\HttpFactoryInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;

/**
 * @package Neomerx\JsonApi
 */
class HeaderParametersParser implements HeaderParametersParserInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var HttpFactoryInterface
     */
    private $factory;

    /**
     * @param HttpFactoryInterface $factory
     */
    public function __construct(HttpFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function parse(ServerRequestInterface $request, $checkContentType = true)
    {
        $acceptHeader      = null;
        $contentTypeHeader = null;

        if ($checkContentType === true) {
            try {
                $header            = $this->getHeader($request, HeaderInterface::HEADER_CONTENT_TYPE);
                $contentTypeHeader = Header::parse($header, HeaderInterface::HEADER_CONTENT_TYPE);
            } catch (InvalidArgumentException $exception) {
                $title  = T::t('Invalid ' . HeaderInterface::HEADER_CONTENT_TYPE . ' header.');
                $error  = new Error(null, null, null, null, $title);
                E::throwException(new E([$error], E::HTTP_CODE_BAD_REQUEST, $exception));
            }
        }

        try {
            $header       = $this->getHeader($request, HeaderInterface::HEADER_ACCEPT);
            $acceptHeader = AcceptHeader::parse($header);
        } catch (InvalidArgumentException $exception) {
            $title  = T::t('Invalid ' . HeaderInterface::HEADER_ACCEPT . ' header.');
            $error  = new Error(null, null, null, null, $title);
            E::throwException(new E([$error], E::HTTP_CODE_BAD_REQUEST, $exception));
        }

        $method = $request->getMethod();

        return $checkContentType === true ?
            $this->factory->createHeaderParameters($method, $acceptHeader, $contentTypeHeader):
            $this->factory->createNoContentHeaderParameters($method, $acceptHeader);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $name
     *
     * @return string
     */
    private function getHeader(ServerRequestInterface $request, $name)
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
