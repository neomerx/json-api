<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi\Models;

/**
 * Copyright 2015-2020 info@neomerx.com
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
 * @property int       postId
 * @property string    title
 * @property string    body
 * @property Author    author
 * @property Comment[] comments
 */
class Post extends \stdClass
{
    /**
     * @param string    $postId
     * @param string    $title
     * @param string    $body
     * @param Author    $author
     * @param Comment[] $comments
     *
     * @return Post
     */
    public static function instance($postId, $title, $body, Author $author, array $comments)
    {
        $post = new self();

        $post->postId   = $postId;
        $post->title    = $title;
        $post->body     = $body;
        $post->author   = $author;
        $post->comments = $comments;

        return $post;
    }
}
