<?php namespace Neomerx\Tests\JsonApi\Data;

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
 * @package Neomerx\Tests\JsonApi
 */
class CommentSchema extends DevSchemaProvider
{
    /**
     * @var bool
     */
    protected $isShowSelfInIncluded = true;

    /**
     * @inheritdoc
     */
    protected $resourceType = 'comments';

    /**
     * @inheritdoc
     */
    protected $baseSelfUrl = 'http://example.com/comments/';

    /**
     * @inheritdoc
     */
    public function getId($comment)
    {
        return $comment->{Comment::ATTRIBUTE_ID};
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($comment)
    {
        return [
            Comment::ATTRIBUTE_BODY => $comment->{Comment::ATTRIBUTE_BODY},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($comment)
    {
        assert('$comment instanceof '.Comment::class);

        $links = [
            Comment::LINK_AUTHOR => [
                self::DATA => isset($comment->{Comment::LINK_AUTHOR}) ? $comment->{Comment::LINK_AUTHOR} : null,
            ],
        ];

        // NOTE: The line(s) below for testing purposes only. Not for production.
        $this->fixLinks($links);

        return $links;
    }
}
