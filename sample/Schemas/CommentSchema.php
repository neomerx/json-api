<?php namespace Neomerx\Samples\JsonApi\Schemas;

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

use Neomerx\JsonApi\Schema\BaseSchema;
use Neomerx\Samples\JsonApi\Models\Comment;

/**
 * @package Neomerx\Samples\JsonApi
 */
class CommentSchema extends BaseSchema
{
    /**
     * @inheritdoc
     */
    protected $resourceType = 'comments';

    /**
     * @inheritdoc
     */
    protected $isShowSelfInIncluded = true;

    /**
     * @inheritdoc
     */
    public function getId($comment): ?string
    {
        /** @var Comment $comment */
        return $comment->commentId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($comment, array $fieldKeysFilter = null): ?array
    {
        /** @var Comment $comment */
        return [
            'body' => $comment->body,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($comment, bool $isPrimary, array $includeRelationships): ?array
    {
        /** @var Comment $comment */
        return [
            'author' => [self::DATA => $comment->author],
        ];
    }
}
