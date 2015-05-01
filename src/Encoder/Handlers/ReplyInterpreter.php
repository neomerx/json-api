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

use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncodingOptionsInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserReplyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Handlers\ReplyInterpreterInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface as Frame;

/**
 * @package Neomerx\JsonApi
 */
class ReplyInterpreter implements ReplyInterpreterInterface
{
    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @var EncodingOptionsInterface|null
     */
    private $options;

    /**
     * @param DocumentInterface             $document
     * @param EncodingOptionsInterface|null $options
     */
    public function __construct(DocumentInterface $document, EncodingOptionsInterface $options = null)
    {
        $this->document = $document;
        $this->options  = $options;
    }

    /**
     * @inheritdoc
     */
    public function handle(ParserReplyInterface $reply)
    {
        $current = $reply->getStack()->end();
        assert('$current !== null');

        if ($reply->getReplyType() === ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED) {
            $this->setResourceCompleted($current);
            return;
        }

        $previous    = $reply->getStack()->end(1);
        $includeRes  = ($current->getLevel() === 1 || $current->isPathIncluded() === true);
        $includeLink = ($current->getLevel() <= 2  || $previous->isPathIncluded() === true);

        assert('$current->getLevel() > 0');

        list($parentIsTarget, $currentIsTarget) = $this->analyzeStackTargets($current, $previous, $this->options);

        $isAddResourceToIncluded = ($includeRes === true  && $currentIsTarget === true);
        $isAddLinkToIncluded     = ($includeLink === true && $parentIsTarget === true);

        // TODO refactor and add support for relation fieldSets

        switch($current->getLevel()) {
            case 1:
                $this->addToData($reply, $current);
                break;
            case 2:
                assert('$previous !== null');
                $this->addLinkToData($reply, $current, $previous);
                if ($isAddResourceToIncluded === true) {
                    $this->addToIncluded($reply, $current);
                }
                break;
            default:
                if ($isAddLinkToIncluded === true) {
                    assert('$previous !== null');
                    $this->addLinkToIncluded($reply, $current, $previous);
                }
                if ($isAddResourceToIncluded === true) {
                    $this->addToIncluded($reply, $current);
                }
                break;
        }
    }

    /**
     * @param ParserReplyInterface $reply
     * @param Frame                $current
     *
     * @return void
     */
    private function addToData(ParserReplyInterface $reply, Frame $current)
    {
        $replyType = $reply->getReplyType();
        switch($replyType) {
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->setNullData();
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->setEmptyData();
                break;
            default:
                assert('$replyType === ' . ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED);
                $resourceObject = $current->getResourceObject();
                assert('$resourceObject !== null');
                $this->document->addToData($resourceObject);
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
            $resourceObject = $current->getResourceObject();
            assert('$resourceObject !== null');
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
    private function addLinkToData(ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        $replyType = $reply->getReplyType();
        $link      = $current->getLinkObject();
        $parent    = $previous->getResourceObject();
        assert('$link !== null && $parent !== null');

        switch($replyType) {
            case ParserReplyInterface::REPLY_TYPE_REFERENCE_STARTED:
                assert($link->isShowAsReference() === true);
                $this->document->addReferenceToData($parent, $link);
                break;
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->addNullLinkToData($parent, $link);
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->addEmptyLinkToData($parent, $link);
                break;
            default:
                assert('$replyType === ' . ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED);
                $resourceObject = $current->getResourceObject();
                assert('$resourceObject !== null');
                $this->document->addLinkToData($parent, $link, $resourceObject);
        }
    }

    /**
     * @param Frame                $current
     * @param Frame                $previous
     * @param ParserReplyInterface $reply
     *
     * @return void
     */
    private function addLinkToIncluded(ParserReplyInterface $reply, Frame $current, Frame $previous)
    {
        $replyType = $reply->getReplyType();
        $link      = $current->getLinkObject();
        $parent    = $previous->getResourceObject();
        assert('$link !== null && $parent !== null');

        switch($replyType) {
            case ParserReplyInterface::REPLY_TYPE_REFERENCE_STARTED:
                assert($link->isShowAsReference() === true);
                $this->document->addReferenceToIncluded($parent, $link);
                break;
            case ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED:
                $this->document->addNullLinkToIncluded($parent, $link);
                break;
            case ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED:
                $this->document->addEmptyLinkToIncluded($parent, $link);
                break;
            default:
                assert('$replyType === ' . ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED);
                $resourceObject = $current->getResourceObject();
                assert('$resourceObject !== null');
                $this->document->addLinkToIncluded($parent, $link, $resourceObject);
        }
    }

    /**
     * @param Frame $current
     *
     * @return void
     */
    private function setResourceCompleted(Frame $current)
    {
        $resourceObject = $current->getResourceObject();
        assert('$resourceObject !== null');
        $this->document->setResourceCompleted($resourceObject);
    }

    /**
     * @param StackFrameReadOnlyInterface      $current
     * @param StackFrameReadOnlyInterface|null $previous
     * @param EncodingOptionsInterface|null    $options
     *
     * @return bool[]
     */
    private function analyzeStackTargets(
        StackFrameReadOnlyInterface $current,
        StackFrameReadOnlyInterface $previous = null,
        EncodingOptionsInterface $options = null
    ) {
        if ($options === null || ($paths = $options->getIncludePaths()) === null) {
            return [true, true];
        }

        $parentIsTarget  = ($previous === null);
        $currentIsTarget = false;
        foreach ($paths as $targetPath) {
            $parentIsTarget  = ($parentIsTarget === true  ? $parentIsTarget  : $previous->getPath()  === $targetPath);
            $currentIsTarget = ($currentIsTarget === true ? $currentIsTarget : $current->getPath() === $targetPath);
            if ($currentIsTarget === true && $parentIsTarget === true) {
                break;
            }
        }

        return [$parentIsTarget, $currentIsTarget];
    }
}
