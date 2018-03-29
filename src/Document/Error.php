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
        $this
            ->setId($idx)
            ->setLink(DocumentInterface::KEYWORD_ERRORS_ABOUT, $aboutLink)
            ->setStatus($status)
            ->setCode($code)
            ->setTitle($title)
            ->setDetail($detail)
            ->setSource($source)
            ->setMeta($meta);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idx;
    }

    /**
     * @param string|int|null $idx
     *
     * @return self
     */
    public function setId($idx): self
    {
        assert($idx === null || is_int($idx) === true || is_string($idx) === true);

        $this->idx = $idx;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * @param string             $name
     * @param LinkInterface|null $link
     *
     * @return self
     */
    public function setLink(string $name, ?LinkInterface $link): self
    {
        if ($link !== null) {
            $this->links[$name] = $link;
        } else {
            unset($this->links[$name]);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return self
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     *
     * @return self
     */
    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return self
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @param null|string $detail
     *
     * @return self
     */
    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @param array|null $source
     *
     * @return self
     */
    public function setSource(?array $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed|null $meta
     *
     * @return self
     */
    public function setMeta($meta): self
    {
        $this->meta = $meta;

        return $this;
    }
}
