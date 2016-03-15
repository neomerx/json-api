<?php namespace Neomerx\JsonApi\Contracts\Codec;

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
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;

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
     *
     * @return void
     */
    public function registerEncoder(MediaTypeInterface $mediaType, Closure $encoderClosure);

    /**
     * Register decoder.
     *
     * @param MediaTypeInterface $mediaType
     * @param Closure            $decoderClosure
     *
     * @return void
     */
    public function registerDecoder(MediaTypeInterface $mediaType, Closure $decoderClosure);

    /**
     * Get encoder.
     *
     * @return EncoderInterface|null
     */
    public function getEncoder();

    /**
     * Set encoder.
     *
     * @param EncoderInterface|Closure $encoder
     *
     * @return void
     */
    public function setEncoder($encoder);

    /**
     * Get decoder.
     *
     * @return DecoderInterface|null
     */
    public function getDecoder();

    /**
     * Set decoder.
     *
     * @param DecoderInterface|Closure $decoder
     *
     * @return DecoderInterface
     */
    public function setDecoder($decoder);

    /**
     * Find best encoder match for 'Accept' header.
     *
     * @param AcceptHeaderInterface $acceptHeader
     *
     * @return void
     */
    public function matchEncoder(AcceptHeaderInterface $acceptHeader);

    /**
     * Find best decoder match for 'Content-Type' header.
     *
     * @param HeaderInterface $contentTypeHeader
     *
     * @return void
     */
    public function matchDecoder(HeaderInterface $contentTypeHeader);

    /**
     * Get media type from 'Accept' header that matched to one of the registered encoder media types.
     *
     * @return AcceptMediaTypeInterface|null
     */
    public function getEncoderHeaderMatchedType();

    /**
     * Get media type that was registered for matched encoder.
     *
     * @return MediaTypeInterface|null
     */
    public function getEncoderRegisteredMatchedType();

    /**
     * Get media type from 'Content-Type' header that matched to one of the registered decoder media types.
     *
     * @return MediaTypeInterface|null
     */
    public function getDecoderHeaderMatchedType();

    /**
     * Get media type that was registered for matched decoder.
     *
     * @return MediaTypeInterface|null
     */
    public function getDecoderRegisteredMatchedType();
}
