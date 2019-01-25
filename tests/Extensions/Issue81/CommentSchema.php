<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue81;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\AuthorIdentity;
use Neomerx\Tests\JsonApi\Data\Models\Comment;
use Neomerx\Tests\JsonApi\Data\Schemas\CommentSchema as ParentSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CommentSchema extends ParentSchema
{
    /**
     * @inheritdoc
     */
    public function getRelationships($comment): iterable
    {
        assert($comment instanceof Comment);

        // emulate situation when we have only ID in relationship (e.g. user ID) and know type.
        $author   = $comment->{Comment::LINK_AUTHOR};
        $authorId = (string)$author->{Author::ATTRIBUTE_ID};

        $authorIdentity = new AuthorIdentity($authorId);

        $hasMeta = property_exists($author, Author::IDENTIFIER_META);
        if ($hasMeta === true) {
            $authorIdentity->setMeta($author->{Author::IDENTIFIER_META});
        }

        return $this->fixDescriptions(
            $comment,
            [
                Comment::LINK_AUTHOR => [self::RELATIONSHIP_DATA => $authorIdentity],
            ]
        );
    }
}
