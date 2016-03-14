<?php namespace Neomerx\JsonApi\Encoder\Handlers;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed for in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserReplyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Handlers\ReplyInterpreterInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface as Frame;

/**
 * @package Neomerx\JsonApi
 */
class ReplyInterpreter implements ReplyInterpreterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @var ParametersAnalyzerInterface
     */
    private $parameterAnalyzer;

    /**
     * @param DocumentInterface           $document
     * @param ParametersAnalyzerInterface $parameterAnalyzer
     */
    public function __construct(DocumentInterface $document, ParametersAnalyzerInterface $parameterAnalyzer)
    {
        $this->document          = $document;
        $this->parameterAnalyzer = $parameterAnalyzer;
    }

    /**
     * @inheritdoc
     */
    public function handle(ParserReplyInterface $reply)
    {
        $current = $reply->getStack()->end();

        if ($reply->getReplyType() === ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED) {
            $this->setResourceCompleted($current);
            return;
        }

        $previous = $reply->getStack()->penult();

        switch ($current->getLevel()) {
            case 1:
                $this->addToData($reply, $current);
                break;
            case 2:
                $rootType = $reply->getStack()->root()->getResource()->getType();
                $this->handleRelationships($rootType, $reply, $current, $previous);
                break;
            default:
                $rootType = $reply->getStack()->root()->getResource()->getType();
                $this->handleIncluded($rootType, $reply, $current, $previous);
                break;
        }
    }

    /**
     * @param string               $rootType
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     * @param Frame                $previous
     */
    protected function handleRelationships($rootType, ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        $this->addToIncludedAndCheckIfParentIsTarget($rootType, $reply, $current, $previous);

        if ($this->isRelationshipInFieldSet($current, $previous) === true) {
            $this->addRelationshipToData($reply, $current, $previous);
        }
    }

    /**
     * @param string               $rootType
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     * @param Frame                $previous
     */
    protected function handleIncluded($rootType, ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        if ($this->addToIncludedAndCheckIfParentIsTarget($rootType, $reply, $current, $previous) === true &&
            $this->isRelationshipInFieldSet($current, $previous) === true
        ) {
            $this->addRelationshipToIncluded($reply, $current, $previous);
        }
    }

    /**
     * @param string               $rootType
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     * @param Frame                $previous
     *
     * @return bool
     */
    private function addToIncludedAndCheckIfParentIsTarget(
        $rootType,
        ParserReplyInterface $reply,
        Frame $current,
        Frame $previous
    ) {
        list($parentIsTarget, $currentIsTarget) = $this->getIfTargets($rootType, $current, $previous);

        if ($currentIsTarget === true) {
            $this->addToIncluded($reply, $current);
        }

        return $parentIsTarget;
    }

    /**
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     *
     * @return void
     */
    private function addToData(ParserReplyInterface $reply, Frame $current)
    {
        switch ($reply->getReplyType()) {
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->setNullData();
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->setEmptyData();
                break;
            case ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED:
                $this->document->addToData($current->getResource());
                break;
        }
    }

    /**
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     *
     * @return void
     */
    private function addToIncluded(ParserReplyInterface $reply, Frame $current)
    {
        if ($reply->getReplyType() === ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED) {
            $resourceObject = $current->getResource();
            $this->document->addToIncluded($resourceObject);
        }
    }

    /**
     * @param Frame                $current
     * @param Frame                $previous
     * @param ParserReplyInterface $reply
     *
     * @return void
     */
    private function addRelationshipToData(ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        $relationship = $current->getRelationship();
        $parent       = $previous->getResource();

        switch ($reply->getReplyType()) {
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->addNullRelationshipToData($parent, $relationship);
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->addEmptyRelationshipToData($parent, $relationship);
                break;
            case ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED:
                $this->document->addRelationshipToData($parent, $relationship, $current->getResource());
                break;
        }
    }

    /**
     * @param Frame                $current
     * @param Frame                $previous
     * @param ParserReplyInterface $reply
     *
     * @return void
     */
    private function addRelationshipToIncluded(ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        $relationship = $current->getRelationship();
        $parent       = $previous->getResource();

        switch ($reply->getReplyType()) {
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->addNullRelationshipToIncluded($parent, $relationship);
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->addEmptyRelationshipToIncluded($parent, $relationship);
                break;
            case ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED:
                $this->document->addRelationshipToIncluded($parent, $relationship, $current->getResource());
                break;
        }
    }

    /**
     * @param Frame $current
     *
     * @return void
     */
    private function setResourceCompleted(Frame $current)
    {
        // Add resource if it is a main resource (even if it has no fields) or
        // if field set allows any fields for this type (filter out resources with no attributes and relationships)
        if ($current->getLevel() === 1 ||
            $this->parameterAnalyzer->hasSomeFields($current->getResource()->getType()) === true
        ) {
            $resourceObject = $current->getResource();
            $this->document->setResourceCompleted($resourceObject);
        }
    }

    /**
     * @param string     $rootType
     * @param Frame      $current
     * @param Frame|null $previous
     *
     * @return bool[]
     */
    private function getIfTargets($rootType, Frame $current, Frame $previous = null)
    {
        $currentIsTarget = $this->parameterAnalyzer->isPathIncluded($current->getPath(), $rootType);
        $parentIsTarget  = ($previous === null ||
            $this->parameterAnalyzer->isPathIncluded($previous->getPath(), $rootType));

        return [$parentIsTarget, $currentIsTarget];
    }

    /**
     * If relationship from 'parent' to 'current' resource passes field set filter.
     *
     * @param Frame $current
     * @param Frame $previous
     *
     * @return bool
     */
    private function isRelationshipInFieldSet(Frame $current, Frame $previous)
    {
        $parentType = $previous->getResource()->getType();
        $parameters = $this->parameterAnalyzer->getParameters();
        if (($fieldSet = $parameters->getFieldSet($parentType)) === null) {
            return true;
        }

        return (in_array($current->getRelationship()->getName(), $fieldSet, true) === true);
    }
}
