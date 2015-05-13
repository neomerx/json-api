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
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParserManager implements ParserManagerInterface
{
    /**
     * @var EncodingParametersInterface
     */
    private $parameters;

    /**
     * @param EncodingParametersInterface $parameters
     */
    public function __construct(EncodingParametersInterface $parameters)
    {
        $this->parameters = $parameters;
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
        $fieldSet = $this->parameters->getFieldSet($type);
        return $fieldSet === null ? null : array_flip(array_values($fieldSet));
    }

    /**
     * @param StackReadOnlyInterface $stack
     *
     * @return bool[]
     */
    private function foundInPaths(StackReadOnlyInterface $stack)
    {
        if ($stack->count() < 2) {
            // top level, no resources ware started to parse yet
            return [true, false];
        } elseif (($includePaths = $this->parameters->getIncludePaths()) === null) {
            // not include path filter => all resources should be considered as targets
            return [true, true];
        }

        $parentIsTarget = $this->parameters->isPathIncluded($stack->penult()->getPath());

        $onTheWay = false;
        $path     = $stack->end()->getPath();
        foreach ($includePaths as $targetPath) {
            if (strpos($targetPath, $path) === 0) {
                $onTheWay = true;
                break;
            }
        }
        return [$onTheWay, $parentIsTarget];
    }
}
