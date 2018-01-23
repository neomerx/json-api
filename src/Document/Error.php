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
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Error implements ErrorInterface
{
    /**
     * @var int|string|null
     */
    private $idx;

    /**
     * @var null|array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    private $links;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $detail;

    /**
     * @var array|null
     */
    private $source;

    /**
     * @var mixed|null
     */
    private $meta;

    /**
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param string|null        $status
     * @param string|null        $code
     * @param string|null        $title
     * @param string|null        $detail
     * @param array|null         $source
     * @param mixed|null         $meta
     */
    public function __construct(
        $idx = null,
        LinkInterface $aboutLink = null,
        string $status = null,
        string $code = null,
        string $title = null,
        string $detail = null,
        array $source = null,
        $meta = null
    ) {
        assert($idx === null || is_int($idx) === true || is_string($idx) === true);

        $this->idx    = $idx;
        $this->links  = ($aboutLink === null ? null : [DocumentInterface::KEYWORD_ERRORS_ABOUT => $aboutLink]);
        $this->status = ($status !== null ? (string)$status : null);
        $this->code   = ($code !== null ? (string)$code : null);
        $this->title  = $title;
        $this->source = $source;
        $this->detail = $detail;
        $this->meta   = $meta;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idx;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
