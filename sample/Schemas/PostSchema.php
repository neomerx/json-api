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
use Neomerx\Samples\JsonApi\Models\Post;

/**
 * @package Neomerx\Samples\JsonApi
 */
class PostSchema extends BaseSchema
{
    /**
     * @inheritdoc
     */
    protected $resourceType = 'posts';

    /**
     * @inheritdoc
     */
    public function getId($post): ?string
    {
        /** @var Post $post */
        return $post->postId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($post, array $fieldKeysFilter = null): ?array
    {
        /** @var Post $post */
        return [
            'title' => $post->title,
            'body'  => $post->body,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($post, bool $isPrimary, array $includeRelationships): ?array
    {
        /** @var Post $post */
        return [
            'author'   => [self::DATA => $post->author],
            'comments' => [self::DATA => $post->comments],
        ];
    }
}
