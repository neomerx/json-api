<?php

namespace Neomerx\JsonApi\Contracts\Exceptions\Renderer;

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * Interface ExceptionRendererInterface
 * @package Neomerx\JsonApi
 */
interface ExceptionRendererInterface
{

    /**
     * @param $statusCode
     * @return $this
     */
    public function withStatusCode($statusCode);

    /**
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers);

    /**
     * @param SupportedExtensionsInterface $extensions
     * @return $this
     */
    public function withSupportedExtensions(SupportedExtensionsInterface $extensions);

    /**
     * @param MediaTypeInterface $mediaType
     * @return $this
     */
    public function withMediaType(MediaTypeInterface $mediaType);

    /**
     * @param EncoderInterface $encoder
     * @return $this
     */
    public function withEncoder(EncoderInterface $encoder);

    /**
     * @param \Exception $e
     * @return mixed
     */
    public function render(\Exception $e);
}
