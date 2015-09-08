<?php

namespace Neomerx\JsonApi\Contracts\Codec;

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

interface EncoderMatchInterface
{

    /**
     * @return EncoderInterface
     */
    public function getEncoder();

    /**
     * Get media type from 'Accept' header that matched to the encoder media types.
     *
     * @return AcceptMediaTypeInterface|null
     */
    public function getHeaderType();

    /**
     * Get media type that was registered for matched encoder.
     *
     * @return MediaTypeInterface
     */
    public function getRegisteredType();
}