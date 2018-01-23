<?php namespace Neomerx\JsonApi\Exceptions;

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

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Document\Error;
use Serializable;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ErrorCollection implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize($this->items);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->items = unserialize($serialized);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    /**
     * @inheritdoc
     *
     * @return ErrorInterface
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $offset === null ? $this->add($value) : $this->items[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    /**
     * @return ErrorInterface[]
     */
    public function getArrayCopy()
    {
        return $this->items;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return self
     */
    public function add(ErrorInterface $error): self
    {
        $this->items[] =$error;

        return $this;
    }

    /**
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addDataError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToData();

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addDataTypeError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToType();

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addDataIdError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToId();

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addAttributesError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToAttributes();

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $name
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addDataAttributeError(
        $name,
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToAttribute($name);

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addRelationshipsError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToRelationships();

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $name
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addRelationshipError(
        $name,
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToRelationship($name);

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $name
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addRelationshipTypeError(
        $name,
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToRelationshipType($name);

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $name
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addRelationshipIdError(
        $name,
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $pointer = $this->getPathToRelationshipId($name);

        return $this->addResourceError($title, $pointer, $detail, $status, $idx, $aboutLink, $code, $meta);
    }

    /**
     * @param string             $name
     * @param string             $title
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return self
     */
    public function addQueryParameterError(
        $name,
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ): self {
        $source = [Error::SOURCE_PARAMETER => $name];
        $error  = new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);

        $this->add($error);

        return $this;
    }

    /** @noinspection PhpTooManyParametersInspection
     * @param string             $title
     * @param string             $pointer
     * @param string|null        $detail
     * @param string|null        $status
     * @param null               $idx
     * @param LinkInterface|null $aboutLink
     * @param string|null        $code
     * @param null               $meta
     *
     * @return self
     */
    protected function addResourceError(
        string $title,
        string $pointer,
        string $detail = null,
        string $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        string $code = null,
        $meta = null
    ): self {
        $source = [Error::SOURCE_POINTER => $pointer];
        $error  = new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);

        $this->add($error);

        return $this;
    }

    /**
     * @return string
     */
    protected function getPathToData(): string
    {
        return '/' . DocumentInterface::KEYWORD_DATA;
    }

    /**
     * @return string
     */
    protected function getPathToType(): string
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_TYPE;
    }

    /**
     * @return string
     */
    protected function getPathToId(): string
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_ID;
    }

    /**
     * @return string
     */
    protected function getPathToAttributes(): string
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_ATTRIBUTES;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToAttribute(string $name): string
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_ATTRIBUTES . '/' . $name;
    }

    /**
     * @return string
     */
    protected function getPathToRelationships(): string
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_RELATIONSHIPS;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationship(string $name): string
    {
        return $this->getPathToRelationships() . '/' . $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationshipType(string $name): string
    {
        return $this->getPathToRelationship($name) . '/' .
            DocumentInterface::KEYWORD_DATA . '/' . DocumentInterface::KEYWORD_TYPE;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationshipId(string $name): string
    {
        return $this->getPathToRelationship($name) . '/' .
            DocumentInterface::KEYWORD_DATA . '/' . DocumentInterface::KEYWORD_ID;
    }
}
