<?php

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

/**
 * @package Neomerx\Samples\JsonApi
 *
 * @property int    commentId
 * @property string body
 * @property Author author
 */
class Comment extends stdClass
{
    /**
     * @param string $commentId
     * @param string $body
     * @param Author $author
     *
     * @return Comment
     */
    public static function instance($commentId, $body, Author $author)
    {
        $comment = new self();

        $comment->commentId = $commentId;
        $comment->body      = $body;
        $comment->author    = $author;

        return $comment;
    }
}
