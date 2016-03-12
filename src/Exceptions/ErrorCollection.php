<?php namespace Neomerx\JsonApi\Exceptions;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
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

use \Countable;
use \ArrayAccess;
use \ArrayObject;
use \Serializable;
use \IteratorAggregate;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;

/**
 * @package Neomerx\JsonApi
 */
class ErrorCollection implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /**
     * @var ArrayObject
     */
    private $items;

    /**
     * ErrorCollection constructor.
     */
    public function __construct()
    {
        $this->items = new ArrayObject();
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->items->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return $this->items->serialize();
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->items->unserialize($serialized);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * @inheritdoc
     *
     * @return Error
     */
    public function offsetGet($offset)
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->items->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->items->offsetUnset($offset);
    }

    /**
     * @return Error[]
     */
    public function getArrayCopy()
    {
        return $this->items->getArrayCopy();
    }

    /**
     * @param Error $error
     *
     * @return $this
     */
    public function add(Error $error)
    {
        $this->items->append($error);

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
     * @return $this
     */
    public function addDataError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ) {
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
     * @return $this
     */
    public function addDataTypeError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ) {
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
     * @return $this
     */
    public function addDataIdError(
        $title,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ) {
        $pointer = $this->getPathToId();

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
     * @return $this
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
    ) {
        $pointer = $this->getPathToAttribute($name);

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
     * @return $this
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
    ) {
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
     * @return $this
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
    ) {
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
     * @return $this
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
    ) {
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
     * @return $this
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
    ) {
        $source = [Error::SOURCE_PARAMETER => $name];
        $error  = new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);

        $this->add($error);

        return $this;
    }

    /** @noinspection PhpTooManyParametersInspection
     * @param string             $title
     * @param string             $pointer
     * @param string|null        $detail
     * @param int|string|null    $status
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $code
     * @param mixed|null         $meta
     *
     * @return $this
     */
    protected function addResourceError(
        $title,
        $pointer,
        $detail = null,
        $status = null,
        $idx = null,
        LinkInterface $aboutLink = null,
        $code = null,
        $meta = null
    ) {
        $source = [Error::SOURCE_POINTER => $pointer];
        $error  = new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);

        $this->add($error);

        return $this;
    }

    /**
     * @return string
     */
    protected function getPathToData()
    {
        return '/' . DocumentInterface::KEYWORD_DATA;
    }

    /**
     * @return string
     */
    protected function getPathToType()
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_TYPE;
    }

    /**
     * @return string
     */
    protected function getPathToId()
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_ID;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToAttribute($name)
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_ATTRIBUTES . '/' . $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationship($name)
    {
        return $this->getPathToData() . '/' . DocumentInterface::KEYWORD_RELATIONSHIPS . '/' . $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationshipType($name)
    {
        return $this->getPathToRelationship($name) . '/' .
            DocumentInterface::KEYWORD_DATA . '/' . DocumentInterface::KEYWORD_TYPE;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPathToRelationshipId($name)
    {
        return $this->getPathToRelationship($name) . '/' .
            DocumentInterface::KEYWORD_DATA . '/' . DocumentInterface::KEYWORD_ID;
    }
}
