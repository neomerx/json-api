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
use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
interface CodecContainerInterface
{
    /** JSON API type */
    const JSON_API_TYPE = 'application/vnd.api+json';

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
     * @param MediaTypeInterface $mediaType
     *
     * @return EncoderInterface|null
     */
    public function getEncoder(MediaTypeInterface $mediaType);

    /**
     * Get decoder.
     *
     * @param MediaTypeInterface $mediaType
     *
     * @return DecoderInterface|null
     */
    public function getDecoder(MediaTypeInterface $mediaType);

    /**
     * Get encoder.
     *
     * @param MediaTypeInterface $mediaType
     *
     * @return bool
     */
    public function isEncoderRegistered(MediaTypeInterface $mediaType);

    /**
     * Get decoder.
     *
     * @param MediaTypeInterface $mediaType
     *
     * @return bool
     */
    public function isDecoderRegistered(MediaTypeInterface $mediaType);
}
