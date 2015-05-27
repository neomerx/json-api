<?php namespace Neomerx\JsonApi\Responses;

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

use \Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Integration\NativeResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\JsonApi
 */
class Responses implements ResponsesInterface
{
    /** Header name that contains format of input data from client */
    const HEADER_CONTENT_TYPE = HeaderInterface::HEADER_CONTENT_TYPE;

    /** Header name that location of newly created resource */
    const HEADER_LOCATION = HeaderInterface::HEADER_LOCATION;

    /** HTTP 'created' status code */
    const HTTP_CREATED = 201;

    /**
     * @var NativeResponsesInterface
     */
    private $responses;

    /**
     * @param NativeResponsesInterface $responses
     */
    public function __construct(NativeResponsesInterface $responses)
    {
        $this->responses = $responses;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(
        $statusCode,
        MediaTypeInterface $mediaType,
        $content = null,
        SupportedExtensionsInterface $supportedExtensions = null
    ) {
        return $this->createResponse($content, $statusCode, $mediaType, $supportedExtensions);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedResponse(
        $location,
        MediaTypeInterface $mediaType,
        $content,
        SupportedExtensionsInterface $supportedExtensions = null
    ) {
        assert('is_string($location)');
        $headers = [self::HEADER_LOCATION => $location];
        return $this->createResponse($content, self::HTTP_CREATED, $mediaType, $supportedExtensions, $headers);
    }

    /**
     * @param string|null                       $content
     * @param int                               $statusCode
     * @param MediaTypeInterface                $mediaType
     * @param SupportedExtensionsInterface|null $supportedExtensions
     * @param array                             $headers
     *
     * @return mixed
     */
    private function createResponse(
        $content,
        $statusCode,
        MediaTypeInterface $mediaType,
        SupportedExtensionsInterface $supportedExtensions = null,
        array $headers = []
    ) {
        assert('is_int($statusCode)');
        $headers[self::HEADER_CONTENT_TYPE] = $this->getContentTypeHeader($mediaType, $supportedExtensions);
        return $this->responses->createResponse($content, $statusCode, $headers);
    }

    /**
     * @param MediaTypeInterface                $mediaType
     * @param SupportedExtensionsInterface|null $supportedExtensions
     *
     * @return string
     */
    private function getContentTypeHeader(
        MediaTypeInterface $mediaType,
        SupportedExtensionsInterface $supportedExtensions = null
    ) {
        $contentType = $mediaType->getMediaType();
        $params      = $mediaType->getParameters();
        $supExt      = $supportedExtensions === null ? null : $supportedExtensions->getExtensions();

        $separator = ';';
        if (isset($params[MediaTypeInterface::PARAM_EXT])) {
            $ext = $params[MediaTypeInterface::PARAM_EXT];
            if (empty($ext) === false) {
                $contentType .= $separator . MediaTypeInterface::PARAM_EXT . '="' . $ext . '"';
                $separator = ',';
            }
        }

        empty($supExt) === true ?:
            $contentType .= $separator . MediaTypeInterface::PARAM_SUPPORTED_EXT . '="' . $supExt . '"';

        return $contentType;
    }
}
