<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi\Schemas;

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

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
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
    public function getType(): string
    {
        return 'posts';
    }

    /**
     * @inheritdoc
     */
    public function getId($post): ?string
    {
        assert($post instanceof Post);

        return (string)$post->postId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($post, ContextInterface $context): iterable
    {
        assert($post instanceof Post);

        return [
            'title' => $post->title,
            'body'  => $post->body,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($post, ContextInterface $context): iterable
    {
        assert($post instanceof Post);

        return [
            'author'   => [
                self::RELATIONSHIP_DATA          => $post->author,
                self::RELATIONSHIP_LINKS_SELF    => false,
                self::RELATIONSHIP_LINKS_RELATED => false,
            ],
            'comments' => [
                self::RELATIONSHIP_DATA          => $post->comments,
                self::RELATIONSHIP_LINKS_SELF    => false,
                self::RELATIONSHIP_LINKS_RELATED => false,
            ],
        ];
    }
}
