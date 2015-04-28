<?php namespace Neomerx\JsonApi\Encoder\Parser;

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
use \Neomerx\JsonApi\Contracts\Encoder\EncodingOptionsInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParserManager implements ParserManagerInterface
{
    /**
     * @var EncodingOptionsInterface
     */
    private $options;

    /**
     * @param EncodingOptionsInterface $options
     */
    public function __construct(EncodingOptionsInterface $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritdoc
     */
    public function isShouldParseResources(LinkObjectInterface $link, StackReadOnlyInterface $stack)
    {
        // TODO consider removing this empty method
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isShouldParseLinks(ResourceObjectInterface $resource, $isCircular, StackReadOnlyInterface $stack)
    {
        list($onTheWay, $parentIsTarget)= $this->foundInPaths($stack);
        $shouldContinue = $onTheWay || ($parentIsTarget && $resource->isShowLinksInIncluded());
        return $shouldContinue;
    }

    /**
     * @inheritdoc
     */
    public function getFieldSet($type)
    {
        $fieldSet = $this->options->getFieldSet($type);
        return $fieldSet === null ? null : array_flip(array_values($fieldSet));
    }

    /**
     * @param StackReadOnlyInterface $stack
     *
     * @return bool[]
     */
    private function foundInPaths(StackReadOnlyInterface $stack)
    {
        list($parentPath, $path) = $this->getStackPaths($stack);

        // top level, no resources ware started to parse yet
        if ($path === null) {
            return [true, false];
        }

        $onTheWay       = false;
        $parentIsTarget = false;
        foreach ($this->options->getIncludePaths() as $targetPath) {
            $onTheWay       = ($onTheWay === true       ? true : strpos($targetPath, $path) === 0);
            $parentIsTarget = ($parentIsTarget === true ? true : $parentPath === $targetPath);
            if ($onTheWay === true && $parentIsTarget === true) {
                break;
            }
        }
        return [$onTheWay, $parentIsTarget];
    }

    /**
     * @param StackReadOnlyInterface $stack
     *
     * @return string[]
     */
    private function getStackPaths(StackReadOnlyInterface $stack)
    {
        // TODO same code in interpreter. refactor

        $path       = null;
        $parentPath = null;
        foreach ($stack as $frame) {
            /** @var StackFrameReadOnlyInterface $frame */
            $level = $frame->getLevel();
            assert('$level > 0');
            switch($level)
            {
                case 1:
                    break;
                case 2:
                    $path = $frame->getLinkObject()->getName();
                    break;
                default:
                    $parentPath = $path;
                    $path .= '.' . $frame->getLinkObject()->getName();
            }
        }
        return [$parentPath, $path];
    }
}
