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

use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
class StackFrame implements StackFrameInterface
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var ResourceObjectInterface
     */
    private $resource;

    /**
     * @var RelationshipObjectInterface
     */
    private $relationship;

    /**
     * @var StackFrameReadOnlyInterface
     */
    private $previous;

    /**
     * @var string
     */
    private $path = null;

    /**
     * @param StackFrameReadOnlyInterface|null $previous
     */
    public function __construct(StackFrameReadOnlyInterface $previous = null)
    {
        settype($level, 'int');

        $level = $previous === null ? 1 : $previous->getLevel() + 1;

        // debug check
        $isOk = ($level <= 2 || ($previous !== null && $previous->getRelationship() !== null));
        $isOk ?: Exceptions::throwLogicException();

        $this->level    = $level;
        $this->previous = $previous;
    }

    /**
     * @inheritdoc
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @inheritdoc
     */
    public function setResource(ResourceObjectInterface $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @inheritdoc
     */
    public function setRelationship(RelationshipObjectInterface $relationship)
    {
        $this->relationship = $relationship;

        $this->setCurrentPath();
    }

    /**
     * @inheritdoc
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @inheritdoc
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set path to current frame.
     */
    private function setCurrentPath()
    {
        if ($this->previous === null || $this->previous->getPath() === null) {
            $this->path = $this->relationship->getName();
        } else {
            $this->path =
                $this->previous->getPath() . DocumentInterface::PATH_SEPARATOR . $this->relationship->getName();
        }
    }
}
