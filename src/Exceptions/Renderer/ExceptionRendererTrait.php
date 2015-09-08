<?php

namespace Neomerx\JsonApi\Exceptions\Renderer;

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Parameters\Headers\MediaType;

/**
 * Class ExceptionRendererTrait
 * @package Neomerx\JsonApi
 */
trait ExceptionRendererTrait
{

    /**
     * @var int|null
     */
    protected $_statusCode;

    /**
     * @var array|null
     */
    protected $_headers;

    /**
     * @var SupportedExtensionsInterface|null
     */
    protected $_extensions;

    /**
     * @var MediaTypeInterface|null
     */
    protected $_mediaType;

    /**
     * @var EncoderInterface|null
     */
    protected $_encoder;

    /**
     * @param $statusCode
     * @return $this
     */
    public function withStatusCode($statusCode)
    {
        $this->_statusCode = (int) $statusCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->hasStatusCode() ? (int) $this->_statusCode : 500;
    }

    /**
     * @return bool
     */
    public function hasStatusCode()
    {
        return $this->isStatusCode($this->_statusCode);
    }

    /**
     * @param $statusCode
     * @return bool
     */
    public function isStatusCode($statusCode)
    {
        return (100 <= $statusCode && 600 > $statusCode);
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers)
    {
        $this->_headers = $headers;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return (array) $this->_headers;
    }

    /**
     * @param SupportedExtensionsInterface $extensions
     * @return $this
     */
    public function withSupportedExtensions(SupportedExtensionsInterface $extensions)
    {
        $this->_extensions = $extensions;

        return $this;
    }

    /**
     * @return SupportedExtensionsInterface|null
     */
    public function getSupportedExtensions()
    {
        return $this->_extensions;
    }

    /**
     * @param MediaTypeInterface $mediaType
     * @return $this
     */
    public function withMediaType(MediaTypeInterface $mediaType)
    {
        $this->_mediaType = $mediaType;

        return $this;
    }

    /**
     * @return MediaTypeInterface
     */
    public function getMediaType()
    {
        if ($this->_mediaType instanceof MediaTypeInterface) {
            return $this->_mediaType;
        }

        return new MediaType(MediaTypeInterface::JSON_API_TYPE, MediaTypeInterface::JSON_API_SUB_TYPE);
    }

    /**
     * @param EncoderInterface $encoder
     * @return $this
     */
    public function withEncoder(EncoderInterface $encoder)
    {
        $this->_encoder = $encoder;

        return $this;
    }

    /**
     * @return EncoderInterface
     */
    public function getEncoder()
    {
        if (!$this->_encoder instanceof EncoderInterface) {
            $this->_encoder = Encoder::instance([]);
        }

        return $this->_encoder;
    }
}
