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

use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;

/**
 * @package Neomerx\JsonApi
 */
interface PositionInterface
{
    /**
     * A symbol used to separate path's parts.
     */
    const PATH_SEPARATOR = DocumentInterface::PATH_SEPARATOR;

    /**
     * Get level (0 for root, 1 for their children, and so on).
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * Get level ('' for root, 'relationship-name' for their children,
     * 'relationship-name.another-name' and so on).
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Get JSON type of the parent parsed result (if parent exists).
     *
     * @return null|string
     */
    public function getParentType(): ?string;

    /**
     * Get parent's relationship where this result is located (if parent exists).
     *
     * @return null|string
     */
    public function getParentRelationship(): ?string;
}
