<?php namespace Neomerx\JsonApi\Encoder\Factory;

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

use \Neomerx\JsonApi\Encoder\Stack\Stack;
use \Neomerx\JsonApi\Encoder\Parser\Parser;
use \Neomerx\JsonApi\Encoder\Stack\StackFrame;
use \Neomerx\JsonApi\Encoder\Parser\ParserReply;
use \Neomerx\JsonApi\Encoder\Parser\ParserManager;
use \Neomerx\JsonApi\Encoder\Parser\ParserEmptyReply;
use \Neomerx\JsonApi\Encoder\Handlers\ReplyInterpreter;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Handlers\HandlerFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
class EncoderFactory implements ParserFactoryInterface, StackFactoryInterface, HandlerFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createReply($replyType, StackReadOnlyInterface $stack)
    {
        return new ParserReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createEmptyReply(
        $replyType,
        StackReadOnlyInterface $stack
    ) {
        return new ParserEmptyReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createParser(ContainerInterface $container, ParserManagerInterface $manager = null)
    {
        return new Parser($this, $this, $container, $manager);
    }

    /**
     * @inheritdoc
     */
    public function createManager(EncodingParametersInterface $parameters)
    {
        return new ParserManager($parameters);
    }

    /**
     * @inheritdoc
     */
    public function createFrame($level, StackFrameReadOnlyInterface $previous = null)
    {
        return new StackFrame($level, $previous);
    }

    /**
     * @inheritdoc
     */
    public function createStack()
    {
        return new Stack($this);
    }

    /**
     * @inheritdoc
     */
    public function createReplyInterpreter(DocumentInterface $document, EncodingParametersInterface $parameters = null)
    {
        return new ReplyInterpreter($document, $parameters);
    }
}
