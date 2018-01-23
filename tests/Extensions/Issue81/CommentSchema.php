<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue81;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Neomerx\Tests\JsonApi\Data\Author;
use Neomerx\Tests\JsonApi\Data\Comment;
use Neomerx\Tests\JsonApi\Data\CommentSchema as ParentSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CommentSchema extends ParentSchema
{
    /**
     * @inheritdoc
     */
    public function getRelationships($comment, bool $isPrimary, array $includeRelationships): ?array
    {
        assert($comment instanceof Comment);

        if (isset($includeRelationships[Comment::LINK_AUTHOR]) === true) {
            $data = $comment->{Comment::LINK_AUTHOR};
        } else {
            /** @var Author $author */
            $author   = $comment->{Comment::LINK_AUTHOR};
            $authorId = $author->{Author::ATTRIBUTE_ID};

            $data = $authorId === null ? null : new AuthorIdentity($authorId);
        }

        $links = [
            Comment::LINK_AUTHOR => [self::DATA => $data],
        ];

        // NOTE: The line(s) below for testing purposes only. Not for production.
        $this->fixLinks($comment, $links);

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource): array
    {
        return [];
    }
}
