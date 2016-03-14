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

use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;

/**
 * @package Neomerx\JsonApi
 */
class ParserManager implements ParserManagerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ParametersAnalyzerInterface
     */
    protected $parameterAnalyzer;

    /**
     * @var array
     */
    private $fieldSetCache;

    /**
     * @param ParametersAnalyzerInterface $parameterAnalyzer
     */
    public function __construct(ParametersAnalyzerInterface $parameterAnalyzer)
    {
        $this->fieldSetCache     = [];
        $this->parameterAnalyzer = $parameterAnalyzer;
    }

    /**
     * @inheritdoc
     */
    public function isShouldParseRelationships(StackReadOnlyInterface $stack)
    {
        if ($stack->count() < 2) {
            // top level, no resources ware started to parse yet
            $shouldContinue = true;
        } else {
            // on the way to included paths
            $currentPath     = $stack->end()->getPath();
            $currentRootType = $stack->root()->getResource()->getType();

            $shouldContinue = $this->parameterAnalyzer->isPathIncluded($currentPath, $currentRootType);
        }

        return $shouldContinue;
    }

    /**
     * @inheritdoc
     */
    public function getIncludeRelationships(StackReadOnlyInterface $stack)
    {
        $currentPath     = $stack->end()->getPath();
        $currentRootType = $stack->root()->getResource()->getType();
        $includePaths    = $this->parameterAnalyzer->getIncludeRelationships($currentPath, $currentRootType);

        return $includePaths;
    }

    /**
     * @inheritdoc
     */
    public function isRelationshipInFieldSet(StackReadOnlyInterface $stack)
    {
        $resourceType     = $stack->penult()->getResource()->getType();
        $resourceFieldSet = $this->getFieldSet($resourceType);

        $inFieldSet = $resourceFieldSet === null ? true : array_key_exists(
            $stack->end()->getRelationship()->getName(),
            $resourceFieldSet
        );

        return $inFieldSet;
    }

    /**
     * @inheritdoc
     */
    public function getFieldSet($type)
    {
        settype($type, 'string');

        if (array_key_exists($type, $this->fieldSetCache) === false) {
            $fieldSet = $this->parameterAnalyzer->getParameters()->getFieldSet($type);
            $this->fieldSetCache[$type] = $fieldSet === null ? null : array_flip(array_values($fieldSet));
        }

        return $this->fieldSetCache[$type];
    }
}
