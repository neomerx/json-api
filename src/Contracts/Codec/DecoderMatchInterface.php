<?php

namespace Neomerx\JsonApi\Contracts\Codec;

use Neomerx\JsonApi\Contracts\Decoder\DecoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

interface DecoderMatchInterface
{

    /**
     * @return DecoderInterface
     */
    public function getDecoder();

    /**
     * Get media type from 'Content-Type' header that matched to the decoder media types.
     *
     * @return AcceptMediaTypeInterface|null
     */
    public function getHeaderType();

    /**
     * Get media type that was registered for matched decoder.
     *
     * @return MediaTypeInterface
     */
    public function getRegisteredType();
}