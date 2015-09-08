<?php

namespace Neomerx\JsonApi\Contracts\Codec;

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

use \Closure;
use \Neomerx\JsonApi\Contracts\Decoder\DecoderInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\AcceptHeaderInterface;

/**
 * @package Neomerx\JsonApi
 */
interface CodecMatcherInterface
{

    /**
     * Register encoder.
     *
     * @param MediaTypeInterface $mediaType
     * @param Closure            $encoderClosure
     * @return $this
     */
    public function registerEncoder(MediaTypeInterface $mediaType, Closure $encoderClosure);

    /**
     * Register decoder.
     *
     * @param MediaTypeInterface $mediaType
     * @param Closure            $decoderClosure
     * @return $this
     */
    public function registerDecoder(MediaTypeInterface $mediaType, Closure $decoderClosure);

    /**
     * @param MediaTypeInterface $mediaType
     * @param Closure $encoder
     * @param Closure $decoder
     * @return $this
     */
    public function registerBoth(MediaTypeInterface $mediaType, Closure $encoder, Closure $decoder);

    /**
     * Register the default media type that should be used if match is not found.
     *
     * A default media type is required for instances where a fallback encoder is required. For example, if an error
     * needs to be encoded but the error was caused by there not being an encoder match, a renderer will need a fallback
     * encoder to use.
     *
     * @param MediaTypeInterface $mediaType
     * @return $this
     */
    public function registerDefault(MediaTypeInterface $mediaType);

    /**
     * @return EncoderMatchInterface
     */
    public function getDefaultEncoder();

    /**
     * Get the matched encoder, or null if no match.
     *
     * @return EncoderMatchInterface|null
     */
    public function getEncoderMatch();

    /**
     * Set the matched encoder.
     *
     * @param EncoderInterface|Closure $encoder
     * @param MediaTypeInterface $mediaType
     * @return $this
     */
    public function setEncoder($encoder, MediaTypeInterface $mediaType);

    /**
     * Get the matched decoder, or null if no match.
     *
     * @return DecoderMatchInterface|null
     */
    public function getDecoderMatch();

    /**
     * Set the matched decoder.
     *
     * @param DecoderInterface|Closure $decoder
     * @param MediaTypeInterface $mediaType
     * @return mixed
     */
    public function setDecoder($decoder, MediaTypeInterface $mediaType);

    /**
     * Find best encoder match for 'Accept' header.
     *
     * @param AcceptHeaderInterface $acceptHeader
     * @return EncoderMatchInterface|null
     *      the encoder that was matched, or null if none.
     */
    public function matchEncoder(AcceptHeaderInterface $acceptHeader);

    /**
     * Find best decoder match for 'Content-Type' header.
     *
     * @param HeaderInterface $contentTypeHeader
     * @return DecoderMatchInterface|null
     *      the decoder that was found, or nll if none.
     */
    public function findDecoder(HeaderInterface $contentTypeHeader);

}
