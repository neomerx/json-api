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
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
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
     * @var int
     */
    private $size;

    /**
     * Constructor.
     *
     * @param StackFactoryInterface $factory
     */
    public function __construct(StackFactoryInterface $factory)
    {
        $this->factory = $factory;
        $this->stack   = [];
        $this->size    = 0;
    }

    /**
     * @inheritdoc
     */
    public function push()
    {
        $frame = $this->factory->createFrame($this->end());
        array_push($this->stack, $frame);
        $this->size++;
        return $frame;
    }

    /**
     * @inheritdoc
     */
    public function pop()
    {
        array_pop($this->stack);
        $this->size <= 0 ?: $this->size--;
    }

    /**
     * @inheritdoc
     */
    public function root()
    {
        return $this->size > 0 ? $this->stack[0] : null;
    }

    /**
     * @inheritdoc
     */
    public function end()
    {
        return $this->size > 0 ? $this->stack[$this->size - 1] : null;
    }

    /**
     * @inheritdoc
     */
    public function penult()
    {
        return $this->size > 1 ? $this->stack[$this->size - 2] : null;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->size;
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
    public function setCurrentResource(ResourceObjectInterface $resource)
    {
        /** @var StackFrameInterface $lastFrame */
        $lastFrame = end($this->stack);
        $lastFrame->setResource($resource);
    }

    /**
     * @inheritdoc
     */
    public function setCurrentRelationship(RelationshipObjectInterface $relationship)
    {
        /** @var StackFrameInterface $lastFrame */
        $lastFrame = end($this->stack);
        $lastFrame->setRelationship($relationship);
    }
}
