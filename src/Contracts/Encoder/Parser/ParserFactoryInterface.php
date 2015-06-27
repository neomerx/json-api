<?php namespace Neomerx\JsonApi\Contracts\Encoder\Parser;

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

use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
interface ParserFactoryInterface
{
    /**
     * Create parser reply.
     *
     * @param int                    $replyType
     * @param StackReadOnlyInterface $stack
     *
     * @return ParserReplyInterface
     */
    public function createReply($replyType, StackReadOnlyInterface $stack);

    /**
     * Create parser empty reply.
     *
     * @param int                    $replyType
     * @param StackReadOnlyInterface $stack
     *
     * @return ParserReplyInterface
     */
    public function createEmptyReply($replyType, StackReadOnlyInterface $stack);

    /**
     * Create parser.
     *
     * @param DataAnalyzerInterface       $analyzer
     * @param ParserManagerInterface|null $manager
     *
     * @return ParserInterface
     */
    public function createParser(DataAnalyzerInterface $analyzer, ParserManagerInterface $manager = null);

    /**
     * Create parser manager.
     *
     * @param EncodingParametersInterface $parameters
     *
     * @return ParserManagerInterface
     */
    public function createManager(EncodingParametersInterface $parameters);

    /**
     * Create data analyzer.
     *
     * @param ContainerInterface $container
     *
     * @return DataAnalyzerInterface
     */
    public function createAnalyzer(ContainerInterface $container);
}
