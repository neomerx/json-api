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

use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncodingOptionsInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;

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
        $current    = $stack->end();
        $previous   = $stack->end(1);
        $path       = ($current === null ? null : $current->getPath());
        $parentPath = ($previous === null ? null : $previous->getPath());

        // top level, no resources ware started to parse yet
        if ($path === null) {
            return [true, false];
        }

        $onTheWay       = false;
        $parentIsTarget = false;
        $includePaths   = $this->options->getIncludePaths();
        if ($includePaths !== null) {
            foreach ($includePaths as $targetPath) {
                $onTheWay       = ($onTheWay === true ? true : strpos($targetPath, $path) === 0);
                $parentIsTarget = ($parentIsTarget === true ? true : $parentPath === $targetPath);
                if ($onTheWay === true && $parentIsTarget === true) {
                    break;
                }
            }
        }
        return [$onTheWay, $parentIsTarget];
    }
}
