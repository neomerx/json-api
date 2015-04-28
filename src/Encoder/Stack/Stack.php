<?php namespace Neomerx\JsonApi\Encoder\Stack;

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

use \ArrayIterator;
use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class Stack implements StackInterface
{
    /**
     * @var array
     */
    private $stack;

    /**
     * @var StackFactoryInterface
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param StackFactoryInterface $factory
     */
    public function __construct(StackFactoryInterface $factory)
    {
        $this->factory = $factory;
        $this->stack   = [];
    }

    /**
     * @inheritdoc
     */
    public function push()
    {
        $frame = $this->factory->createFrame($this->count() + 1);
        array_push($this->stack, $frame);
        return $frame;
    }

    /**
     * @inheritdoc
     */
    public function pop()
    {
        array_pop($this->stack);
    }

    /**
     * @inheritdoc
     */
    public function end($number = 0)
    {
        assert('is_int($number) && $number >= 0');

        $count = $this->count();
        $frameIndex = $count - 1 - $number;
        return (0 <= $frameIndex && $frameIndex < $count ? $this->stack[$frameIndex] : null);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->stack);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->stack);
    }

    /**
     * @inheritdoc
     */
    public function setCurrentResourceObject(ResourceObjectInterface $resourceObject)
    {
        /** @var StackFrameInterface $lastFrame */
        $lastFrame = end($this->stack);
        assert('is_null($lastFrame) === false');
        $lastFrame->setResourceObject($resourceObject);
    }

    /**
     * @inheritdoc
     */
    public function setCurrentLinkObject(LinkObjectInterface $linkObject)
    {
        /** @var StackFrameInterface $lastFrame */
        $lastFrame = end($this->stack);
        assert('is_null($lastFrame) === false');
        $lastFrame->setLinkObject($linkObject);
    }
}
