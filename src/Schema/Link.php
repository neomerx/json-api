<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Schema;

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
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
class Link implements LinkInterface
{
    /**
     * @var bool
     */
    private $isSubUrl;

    /**
     * @var string
     */
    private $value;

    /**
     * @var bool
     */
    private $hasMeta;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @param bool   $isSubUrl
     * @param string $value
     * @param bool   $hasMeta
     * @param mixed  $meta
     */
    public function __construct(bool $isSubUrl, string $value, bool $hasMeta, $meta = null)
    {
        $this->isSubUrl = $isSubUrl;
        $this->value    = $value;
        $this->hasMeta  = $hasMeta;
        $this->meta     = $meta;
    }

    /**
     * @inheritdoc
     */
    public function canBeShownAsString(): bool
    {
        return $this->hasMeta() === false;
    }

    /**
     * @inheritdoc
     */
    public function getStringRepresentation(string $prefix): string
    {
        \assert($this->canBeShownAsString() === true);

        return $this->buildUrl($prefix);
    }

    /**
     * @inheritdoc
     */
    public function getArrayRepresentation(string $prefix): array
    {
        \assert($this->canBeShownAsString() === false);

        return [
            DocumentInterface::KEYWORD_HREF => $this->buildUrl($prefix),
            DocumentInterface::KEYWORD_META => $this->getMeta(),
        ];
    }

    /**
     * If link contains sub-URL value and URL prefix should be added.
     *
     * @return bool
     */
    private function isSubUrl(): bool
    {
        return $this->isSubUrl;
    }

    /**
     * Get link’s URL value (full URL or sub-URL).
     *
     * @return string
     */
    private function getValue(): string
    {
        return $this->value;
    }

    /**
     * If link has meta information.
     *
     * @return bool
     */
    private function hasMeta(): bool
    {
        return $this->hasMeta;
    }

    /**
     * Get meta information.
     *
     * @return mixed
     */
    private function getMeta()
    {
        \assert($this->hasMeta());

        return $this->meta;
    }

    /**
     * @param string $prefix
     *
     * @return string
     */
    protected function buildUrl(string $prefix): string
    {
        return $this->isSubUrl() ? $prefix . $this->getValue() : $this->getValue();
    }
}
