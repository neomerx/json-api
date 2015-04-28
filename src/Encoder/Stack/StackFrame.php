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
     * @param int $level
     */
    public function __construct($level)
    {
        assert('is_int($level) && $level > 0');
        $this->level = $level;
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
}
