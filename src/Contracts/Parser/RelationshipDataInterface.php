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
interface RelationshipDataInterface
{
    /**
     * If data is a collection.
     *
     * @return bool
     */
    public function isCollection(): bool;

    /**
     * If data is null.
     *
     * @return bool
     */
    public function isNull(): bool;

    /**
     * If data is a resource.
     *
     * @return bool
     */
    public function isResource(): bool;

    /**
     * If data is an identifier.
     *
     * @return bool
     */
    public function isIdentifier(): bool;

    /**
     * Get identifier from the relationship (if is a resource or an identifier).
     *
     * @return IdentifierInterface
     */
    public function getIdentifier(): IdentifierInterface;

    /**
     * Get identifiers from the relationship (if is a collection).
     *
     * @see IdentifierInterface
     *
     * @return iterable
     */
    public function getIdentifiers(): iterable;

    /**
     * Get resource from the relationship (if is a resource).
     *
     * @return ResourceInterface
     */
    public function getResource(): ResourceInterface;

    /**
     * Get resources from the relationship (if is a collection).
     *
     * @see ResourceInterface
     *
     * @return iterable
     */
    public function getResources(): iterable;
}
