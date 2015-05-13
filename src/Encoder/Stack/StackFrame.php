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

use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameInterface;
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
    private $resourceObject;

    /**
     * @var LinkObjectInterface
     */
    private $linkObject;

    /**
     * @var StackFrameReadOnlyInterface
     */
    private $previous;

    /**
     * @var string
     */
    private $path = null;

    /**
     * @var bool|null
     */
    private $isPathIncluded = null;

    /**
     * @param int                              $level
     * @param StackFrameReadOnlyInterface|null $previous
     */
    public function __construct($level, StackFrameReadOnlyInterface $previous = null)
    {
        settype($level, 'int');
        assert(
            '$level > 0 &&'.
            '(($level === 1 && $previous === null) || '.
            '($level > 1 && $previous !== null && $level === $previous->getLevel() + 1)) &&'.
            '($level <= 2 || $previous->getLinkObject() !== null)'
        );

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
    public function setResourceObject(ResourceObjectInterface $resourceObject)
    {
        $this->resourceObject = $resourceObject;
    }

    /**
     * @inheritdoc
     */
    public function setLinkObject(LinkObjectInterface $linkObject)
    {
        $this->linkObject = $linkObject;

        $this->setCurrentPath();
        $this->setIsPathToCurrentIsIncluded();
    }

    /**
     * @inheritdoc
     */
    public function getResourceObject()
    {
        return $this->resourceObject;
    }

    /**
     * @inheritdoc
     */
    public function getLinkObject()
    {
        return $this->linkObject;
    }

    /**
     * @inheritdoc
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @inheritdoc
     */
    public function isPathIncluded()
    {
        return $this->isPathIncluded;
    }

    /**
     * Set path to current frame.
     */
    private function setCurrentPath()
    {
        if ($this->previous === null || $this->previous->getPath() === null) {
            $this->path = $this->linkObject->getName();
        } else {
            $this->path = $this->previous->getPath() . '.' . $this->linkObject->getName();
        }
    }

    /**
     * Determine if all elements on the path to current frame should be included.
     */
    private function setIsPathToCurrentIsIncluded()
    {
        assert('$this->level > 1');
        if ($this->level === 2) {
            $this->isPathIncluded = $this->linkObject->isShouldBeIncluded();
        } elseif ($this->level > 2) {
            $isPreviousIncluded = $this->previous->isPathIncluded();
            assert('is_bool($isPreviousIncluded)');
            $this->isPathIncluded = $isPreviousIncluded && $this->linkObject->isShouldBeIncluded();
        }
    }
}
