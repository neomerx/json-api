<?php namespace Neomerx\JsonApi\Codec;

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
use \Neomerx\JsonApi\Contracts\Codec\CodecContainerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
class CodecContainer implements CodecContainerInterface
{
    /**
     * @var array Supported JSON API media types with extensions and their combinations for responses.
     *
     * Servers may support multiple media types at any endpoint. For example, a server may choose to
     * support text/html in order to simplify viewing content via a web browser.
     *
     * JSON API specifications says that input requests might ask output in combination of formats (e.g. "ext1,ext2"
     * which means it should be formatted according to the extensions "ext1" and "ext2".
     *
     * Note: Since extensions can contradict one another or have interactions that can be resolved in many
     * equally plausible ways, it is the responsibility of the server to decide which extensions are compatible,
     * and it is the responsibility of the designer of each implementation of this specification to describe
     * extension interoperability rules which are applicable to that implementation.
     */
    private $outputMediaTypes;

    /**
     * @var array Supported JSON API extensions and their combinations for requests.
     * Similar to supported media types for responses.
     */
    private $inputMediaTypes;

    /**
     * @inheritdoc
     */
    public function registerEncoder(MediaTypeInterface $mediaType, Closure $encoderClosure)
    {
        $this->outputMediaTypes[$mediaType->getMediaType()][$mediaType->getExtensions()] = $encoderClosure;
    }

    /**
     * @inheritdoc
     */
    public function registerDecoder(MediaTypeInterface $mediaType, Closure $decoderClosure)
    {
        $this->inputMediaTypes[$mediaType->getMediaType()][$mediaType->getExtensions()] = $decoderClosure;
    }

    /**
     * @inheritdoc
     */
    public function getEncoder(MediaTypeInterface $mediaType)
    {
        return $this->getItem($mediaType, $this->outputMediaTypes);
    }

    /**
     * @inheritdoc
     */
    public function getDecoder(MediaTypeInterface $mediaType)
    {
        return $this->getItem($mediaType, $this->inputMediaTypes);
    }

    /**
     * @inheritdoc
     */
    public function isEncoderRegistered(MediaTypeInterface $mediaType)
    {
        return isset($this->outputMediaTypes[$mediaType->getMediaType()][$mediaType->getExtensions()]);
    }

    /**
     * @inheritdoc
     */
    public function isDecoderRegistered(MediaTypeInterface $mediaType)
    {
        return isset($this->inputMediaTypes[$mediaType->getMediaType()][$mediaType->getExtensions()]);
    }

    /**
     * @param MediaTypeInterface $mediaType
     * @param array              $collection
     *
     * @return mixed
     */
    private function getItem(MediaTypeInterface $mediaType, array $collection)
    {
        $type       = $mediaType->getMediaType();
        $extensions = $mediaType->getExtensions();

        /** @var Closure|null $closure */
        $closure = isset($collection[$type][$extensions]) === false ? null : $collection[$type][$extensions];
        return $closure !== null ? $closure() : null;
    }
}
