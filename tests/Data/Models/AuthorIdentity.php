<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Models;

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

use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AuthorIdentity implements IdentifierInterface
{
    /**
     * @var string
     */
    private $index;

    /**
     * @var bool
     */
    private $hasMeta = false;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @param string $index
     */
    public function __construct(string $index)
    {
        $this->index = $index;
    }

    /**
     * Get identifier's type.
     *
     * @return string
     */
    public function getType(): string
    {
        return 'people';
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->index;
    }

    /**
     * @inheritdoc
     */
    public function hasIdentifierMeta(): bool
    {
        return $this->hasMeta;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifierMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     *
     * @return AuthorIdentity
     */
    public function setMeta($meta)
    {
        $this->meta    = $meta;
        $this->hasMeta = true;

        return $this;
    }
}
