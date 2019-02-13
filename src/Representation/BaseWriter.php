<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Representation;

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

use Neomerx\JsonApi\Contracts\Representation\BaseWriterInterface;
use Neomerx\JsonApi\Contracts\Schema\BaseLinkInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class BaseWriter implements BaseWriterInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    private $urlPrefix;

    /**
     * @var bool
     */
    private $isDataAnArray;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * @inheritdoc
     */
    public function setDataAsArray(): BaseWriterInterface
    {
        \assert($this->isDataAnArray === false);
        \assert(\array_key_exists(DocumentInterface::KEYWORD_DATA, $this->data) === false);

        $this->data[DocumentInterface::KEYWORD_DATA] = [];
        $this->isDataAnArray                         = true;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDocument(): array
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function setMeta($meta): BaseWriterInterface
    {
        \assert(\is_resource($meta) === false);

        $this->data[DocumentInterface::KEYWORD_META] = $meta;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setJsonApiVersion(string $version): BaseWriterInterface
    {
        $this->data[DocumentInterface::KEYWORD_JSON_API][DocumentInterface::KEYWORD_VERSION] = $version;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setJsonApiMeta($meta): BaseWriterInterface
    {
        \assert(\is_resource($meta) === false);

        $this->data[DocumentInterface::KEYWORD_JSON_API][DocumentInterface::KEYWORD_META] = $meta;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setUrlPrefix(string $prefix): BaseWriterInterface
    {
        $this->urlPrefix = $prefix;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setLinks(iterable $links): BaseWriterInterface
    {
        $representation = $this->getLinksRepresentation(
            $this->urlPrefix,
            $links
        );

        if (empty($representation) === false) {
            $this->data[DocumentInterface::KEYWORD_LINKS] = $representation;
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setProfile(iterable $links): BaseWriterInterface
    {
        $representation = $this->getLinksListRepresentation(
            $this->urlPrefix,
            $links
        );

        if (empty($representation) === false) {
            $this->data[DocumentInterface::KEYWORD_LINKS][DocumentInterface::KEYWORD_PROFILE] = $representation;
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        $this->data          = [];
        $this->urlPrefix     = '';
        $this->isDataAnArray = false;
    }

    /**
     * @return string
     */
    protected function getUrlPrefix(): string
    {
        return $this->urlPrefix;
    }

    /**
     * @param null|string $prefix
     * @param iterable    $links
     *
     * @return array
     */
    protected function getLinksRepresentation(?string $prefix, iterable $links): array
    {
        $result = [];

        foreach ($links as $name => $link) {
            \assert($link instanceof LinkInterface);
            $result[$name] = $link->canBeShownAsString() === true ?
                $link->getStringRepresentation($prefix) : $link->getArrayRepresentation($prefix);
        }

        return $result;
    }

    /**
     * @param null|string $prefix
     * @param iterable    $links
     *
     * @return array
     */
    protected function getLinksListRepresentation(?string $prefix, iterable $links): array
    {
        $result = [];

        foreach ($links as $link) {
            \assert($link instanceof BaseLinkInterface);
            $result[] = $link->canBeShownAsString() === true ?
                $link->getStringRepresentation($prefix) : $link->getArrayRepresentation($prefix);
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isDataAnArray(): bool
    {
        return $this->isDataAnArray;
    }
}
