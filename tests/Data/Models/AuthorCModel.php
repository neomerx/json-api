<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Models;

/**
 * Copyright 2015-2020 info@neomerx.com
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

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AuthorCModel implements ArrayAccess, IteratorAggregate
{
    const ATTRIBUTE_ID = 'author_id';
    const ATTRIBUTE_FIRST_NAME = 'first_name';
    const ATTRIBUTE_LAST_NAME = 'last_name';
    const LINK_COMMENTS = 'comments';

    /**
     * Resource properties.
     *
     * @var array
     */
    private $properties = [];

    /**
     * @param int        $identity
     * @param string     $firstName
     * @param string     $lastName
     * @param array|null $comments
     */
    public function __construct(int $identity, string $firstName, string $lastName, array $comments = null)
    {
        $this[self::ATTRIBUTE_ID]         = $identity;
        $this[self::ATTRIBUTE_FIRST_NAME] = $firstName;
        $this[self::ATTRIBUTE_LAST_NAME]  = $lastName;

        if ($comments !== null) {
            $this[self::LINK_COMMENTS] = $comments;
        }
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->properties);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->properties);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->properties[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->properties[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset): void
    {
        unset($this->properties[$offset]);
    }
}
