<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Http;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class BaseResponses implements ResponsesInterface
{
    /** Header name that contains format of input data from client */
    const HEADER_CONTENT_TYPE = HeaderParametersParserInterface::HEADER_CONTENT_TYPE;

    /** Header name that location of newly created resource */
    const HEADER_LOCATION = 'Location';

    /**
     * Create HTTP response.
     *
     * @param string|null $content
     * @param int         $statusCode
     * @param array       $headers
     *
     * @return mixed
     */
    abstract protected function createResponse(?string $content, int $statusCode, array $headers);

    /**
     * @return EncoderInterface
     */
    abstract protected function getEncoder(): EncoderInterface;

    /**
     * @return MediaTypeInterface
     */
    abstract protected function getMediaType(): MediaTypeInterface;

    /**
     * @inheritdoc
     */
    public function getContentResponse($data, int $statusCode = self::HTTP_OK, array $headers = [])
    {
        $content = $this->getEncoder()->encodeData($data);

        return $this->createJsonApiResponse($content, $statusCode, $headers, true);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedResponse($resource, string $url, array $headers = [])
    {
        $content                        = $this->getEncoder()->encodeData($resource);
        $headers[self::HEADER_LOCATION] = $url;

        return $this->createJsonApiResponse($content, self::HTTP_CREATED, $headers, true);
    }

    /**
     * @inheritdoc
     */
    public function getCodeResponse(int $statusCode, array $headers = [])
    {
        return $this->createJsonApiResponse(null, $statusCode, $headers, false);
    }

    /**
     * @inheritdoc
     */
    public function getMetaResponse($meta, int $statusCode = self::HTTP_OK, array $headers = [])
    {
        $content = $this->getEncoder()->encodeMeta($meta);

        return $this->createJsonApiResponse($content, $statusCode, $headers, true);
    }

    /**
     * @inheritDoc
     */
    public function getIdentifiersResponse($data, int $statusCode = self::HTTP_OK, array $headers = [])
    {
        $content = $this->getEncoder()->encodeIdentifiers($data);

        return $this->createJsonApiResponse($content, $statusCode, $headers, true);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getErrorResponse($errors, int $statusCode = self::HTTP_BAD_REQUEST, array $headers = [])
    {
        if (\is_iterable($errors) === true) {
            /** @var iterable $errors */
            $content = $this->getEncoder()->encodeErrors($errors);
        } else {
            \assert($errors instanceof ErrorInterface);
            $content = $this->getEncoder()->encodeError($errors);
        }

        return $this->createJsonApiResponse($content, $statusCode, $headers, true);
    }

    /**
     * @param string|null $content
     * @param int         $statusCode
     * @param array       $headers
     * @param bool        $addContentType
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    protected function createJsonApiResponse(
        ?string $content,
        int $statusCode,
        array $headers = [],
        $addContentType = true
    ) {
        if ($addContentType === true) {
            $headers[self::HEADER_CONTENT_TYPE] = $this->getMediaType()->getMediaType();
        }

        return $this->createResponse($content, $statusCode, $headers);
    }
}
