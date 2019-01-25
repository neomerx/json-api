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
 *
 * A quick review of RFC 2616 (14.1 Accept)
 *
 * Accept header has the following structure
 *
 * Accept: type/subtype;media=parameters;q=1.0, ... (other comma separated media types)
 *           ^    ^                      ^
 *           |    |                      |___ Special 'q' parameter sets quality factor and separates media(-range/type)
 *           |    |                           parameters from accept extension parameters.
 *           |    |
 *           ---------- Media type and subtype
 */
interface AcceptMediaTypeInterface extends MediaTypeInterface
{
    /**
     * Get quality factor.
     *
     * @return float Quality factor [0 .. 1]
     */
    public function getQuality(): float;

    /**
     * Get initial position of the media type in header (needed for stable sorting).
     *
     * @return int
     */
    public function getPosition(): int;
}
