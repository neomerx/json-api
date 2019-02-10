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
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

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
    private $index;

    /**
     * @var null|iterable
     */
    private $links;

    /**
     * @var null|iterable
     */
    private $typeLinks;

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
     * @var bool
     */
    private $hasMeta;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param iterable|null      $typeLinks
     * @param string|null        $status
     * @param string|null        $code
     * @param string|null        $title
     * @param string|null        $detail
     * @param array|null         $source
     * @param bool               $hasMeta
     * @param mixed              $meta
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $idx = null,
        LinkInterface $aboutLink = null,
        ?iterable $typeLinks = null,
        string $status = null,
        string $code = null,
        string $title = null,
        string $detail = null,
        array $source = null,
        bool $hasMeta = false,
        $meta = null
    ) {
        $this
            ->setId($idx)
            ->setLink(DocumentInterface::KEYWORD_ERRORS_ABOUT, $aboutLink)
            ->setTypeLinks($typeLinks)
            ->setStatus($status)
            ->setCode($code)
            ->setTitle($title)
            ->setDetail($detail)
            ->setSource($source);

        if (($this->hasMeta = $hasMeta) === true) {
            $this->setMeta($meta);
        }
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->index;
    }

    /**
     * @param string|int|null $index
     *
     * @return self
     */
    public function setId($index): self
    {
        \assert($index === null || \is_int($index) === true || \is_string($index) === true);

        $this->index = $index;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): ?iterable
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getTypeLinks(): ?iterable
    {
        return $this->typeLinks;
    }

    /**
     * @param string             $name
     * @param LinkInterface|null $link
     *
     * @return self
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
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
     * @param iterable|null $typeLinks
     *
     * @return self
     */
    public function setTypeLinks(?iterable $typeLinks): self
    {
        $this->typeLinks = $typeLinks;

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
    public function hasMeta(): bool
    {
        return $this->hasMeta;
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
        $this->hasMeta = true;
        $this->meta    = $meta;

        return $this;
    }
}
