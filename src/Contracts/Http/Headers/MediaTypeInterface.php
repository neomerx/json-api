<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Http\Headers;

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

/**
 * @package Neomerx\JsonApi
 */
interface MediaTypeInterface
{
    /** JSON API type */
    const JSON_API_MEDIA_TYPE = 'application/vnd.api+json';

    /** JSON API type */
    const JSON_API_TYPE = 'application';

    /** JSON API type */
    const JSON_API_SUB_TYPE = 'vnd.api+json';

    /**
     * Get media type (no subtype).
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get media subtype.
     *
     * @return string
     */
    public function getSubType(): string;

    /**
     * Get full media type (type/subtype).
     *
     * @return string
     */
    public function getMediaType(): string;

    /**
     * Get media type parameters.
     *
     * @return array<string,string>|null
     */
    public function getParameters(): ?array;

    /**
     * Compare media types.
     *
     * @param MediaTypeInterface $mediaType
     *
     * @return bool
     */
    public function matchesTo(MediaTypeInterface $mediaType): bool;

    /**
     * Compare media types.
     *
     * @param MediaTypeInterface $mediaType
     *
     * @return bool
     */
    public function equalsTo(MediaTypeInterface $mediaType): bool;
}
