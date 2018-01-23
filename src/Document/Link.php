<?php namespace Neomerx\JsonApi\Document;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Link implements LinkInterface
{
    /**
     * @var string
     */
    private $subHref;

    /**
     * @var mixed|null
     */
    private $meta;

    /**
     * @var bool
     */
    private $treatAsHref;

    /**
     * @param string     $subHref
     * @param mixed|null $meta
     * @param bool       $treatAsHref If $subHref is a full URL and must not be concatenated with other URLs.
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(string $subHref, $meta = null, bool $treatAsHref = false)
    {
        $this->subHref     = $subHref;
        $this->meta        = $meta;
        $this->treatAsHref = $treatAsHref;
    }

    /**
     * @inheritdoc
     */
    public function getSubHref(): string
    {
        return $this->subHref;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function isTreatAsHref(): bool
    {
        return $this->treatAsHref;
    }

    /**
     * @inheritdoc
     */
    public function hasMeta(): bool
    {
        return $this->getMeta() !== null;
    }

    /**
     * @inheritdoc
     */
    public function getHref(string $prefix = null): string
    {
        return $this->isTreatAsHref() === true ? $this->getSubHref() : $prefix . $this->getSubHref();
    }

    /**
     * @inheritdoc
     */
    public function getHrefWithMeta(string $prefix = null): array
    {
        return [
            DocumentInterface::KEYWORD_HREF => $this->getHref($prefix),
            DocumentInterface::KEYWORD_META => $this->getMeta(),
        ];
    }
}
