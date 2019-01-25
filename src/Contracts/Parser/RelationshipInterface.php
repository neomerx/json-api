<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Parser;

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
interface RelationshipInterface extends ParsedResultInterface
{
    /**
     * If relationship has data.
     *
     * @return bool
     */
    public function hasData(): bool;

    /**
     * Get relationship data.
     *
     * @return RelationshipDataInterface
     */
    public function getData(): RelationshipDataInterface;

    /**
     * If relationship has links.
     *
     * @return bool
     */
    public function hasLinks(): bool;

    /**
     * Get relationship links.
     *
     * @see LinkInterface
     *
     * @return iterable
     */
    public function getLinks(): iterable;

    /**
     * If relationship has meta.
     *
     * @return bool
     */
    public function hasMeta(): bool;

    /**
     * Get relationship meta.
     *
     * @return mixed
     */
    public function getMeta();
}
