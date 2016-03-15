<?php namespace Neomerx\JsonApi\Http;

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

use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Exceptions\ErrorCollection;
use \Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class Responses implements ResponsesInterface
{
    /** Header name that contains format of input data from client */
    const HEADER_CONTENT_TYPE = HeaderInterface::HEADER_CONTENT_TYPE;

    /** Header name that location of newly created resource */
    const HEADER_LOCATION = HeaderInterface::HEADER_LOCATION;

    /**
     * Create HTTP response.
     *
     * @param string|null $content
     * @param int         $statusCode
     * @param array       $headers
     *
     * @return mixed
     */
    abstract protected function createResponse($content, $statusCode, array $headers);

    /**
     * @return EncoderInterface
     */
    abstract protected function getEncoder();

    /**
     * @return string|null
     */
    abstract protected function getUrlPrefix();

    /**
     * @return EncodingParametersInterface|null
     */
    abstract protected function getEncodingParameters();

    /**
     * @return ContainerInterface
     */
    abstract protected function getSchemaContainer();

    /**
     * @return SupportedExtensionsInterface|null
     */
    abstract protected function getSupportedExtensions();

    /**
     * @return MediaTypeInterface
     */
    abstract protected function getMediaType();

    /**
     * @inheritdoc
     */
    public function getContentResponse($data, $statusCode = self::HTTP_OK, $links = null, $meta = null)
    {
        $encoder = $this->getEncoder();
        $links === null ?: $encoder->withLinks($links);
        $meta === null ?: $encoder->withMeta($meta);
        $content = $encoder->encodeData($data, $this->getEncodingParameters());

        return $this->createJsonApiResponse($content, $statusCode);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedResponse($resource, $links = null, $meta = null)
    {
        $encoder = $this->getEncoder();
        $links === null ?: $encoder->withLinks($links);
        $meta === null ?: $encoder->withMeta($meta);
        $content = $encoder->encodeData($resource, $this->getEncodingParameters());
        $headers = [self::HEADER_LOCATION => $this->getResourceLocationUrl($resource)];

        return $this->createJsonApiResponse($content, self::HTTP_CREATED, $headers);
    }

    /**
     * @inheritdoc
     */
    public function getCodeResponse($statusCode)
    {
        return $this->createJsonApiResponse(null, $statusCode);
    }

    /**
     * @inheritdoc
     */
    public function getMetaResponse($meta, $statusCode = self::HTTP_OK)
    {
        $encoder = $this->getEncoder();
        $content = $encoder->encodeMeta($meta);

        return $this->createJsonApiResponse($content, $statusCode);
    }

    /**
     * @inheritdoc
     */
    public function getErrorResponse($errors, $statusCode = self::HTTP_BAD_REQUEST)
    {
        if ($errors instanceof ErrorCollection || is_array($errors) === true) {
            /** @var Error[] $errors */
            $content = $this->getEncoder()->encodeErrors($errors);
        } else {
            /** @var Error $errors */
            $content = $this->getEncoder()->encodeError($errors);
        }

        return $this->createJsonApiResponse($content, $statusCode);
    }

    /**
     * @param mixed $resource
     *
     * @return string
     */
    protected function getResourceLocationUrl($resource)
    {
        $resSubUrl = $this->getSchemaContainer()->getSchema($resource)->getSelfSubLink($resource)->getSubHref();
        $urlPrefix = $this->getUrlPrefix();
        $location  = $urlPrefix . $resSubUrl;

        return $location;
    }

    /**
     * @param string|null $content
     * @param int         $statusCode
     * @param array       $headers
     *
     * @return mixed
     */
    protected function createJsonApiResponse($content, $statusCode, array $headers = [])
    {
        $mediaType   = $this->getMediaType();
        $contentType = $mediaType->getMediaType();
        $params      = $mediaType->getParameters();

        $separator = ';';
        if (isset($params[MediaTypeInterface::PARAM_EXT])) {
            $ext = $params[MediaTypeInterface::PARAM_EXT];
            if (empty($ext) === false) {
                $contentType .= $separator . MediaTypeInterface::PARAM_EXT . '="' . $ext . '"';
                $separator = ',';
            }
        }

        $extensions = $this->getSupportedExtensions();
        if ($extensions !== null && ($list = $extensions->getExtensions()) !== null && empty($list) === false) {
            $contentType .= $separator . MediaTypeInterface::PARAM_SUPPORTED_EXT . '="' . $list . '"';
        }

        $headers[self::HEADER_CONTENT_TYPE] = $contentType;

        return $this->createResponse($content, $statusCode, $headers);
    }
}
