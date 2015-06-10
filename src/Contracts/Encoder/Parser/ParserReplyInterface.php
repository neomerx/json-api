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

use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
interface ParserReplyInterface
{
    /** Indicates resource description started */
    const REPLY_TYPE_RESOURCE_STARTED       = 0;
    /** Indicates resource description started */
    const REPLY_TYPE_NULL_RESOURCE_STARTED  = 1;
    /** Indicates resource description started */
    const REPLY_TYPE_EMPTY_RESOURCE_STARTED = 2;
    /** Indicates resource description completed */
    const REPLY_TYPE_RESOURCE_COMPLETED     = 3;

    /**
     * Get reply type.
     *
     * @return int
     */
    public function getReplyType();

    /**
     * Get stack for parse reply.
     *
     * @return StackReadOnlyInterface
     */
    public function getStack();
}
