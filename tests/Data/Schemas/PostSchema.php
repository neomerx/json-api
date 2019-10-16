<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Schemas;

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

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\Tests\JsonApi\Data\Models\Post;
use function assert;
use function property_exists;

/**
 * @package Neomerx\Tests\JsonApi
 */
class PostSchema extends DevSchema
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
    public function getId($resource): ?string
    {
        assert($resource instanceof Post);

        $index = $resource->{Post::ATTRIBUTE_ID};

        return $index === null ? $index : (string)$index;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        assert($resource instanceof Post);

        return [
            Post::ATTRIBUTE_TITLE => $resource->{Post::ATTRIBUTE_TITLE},
            Post::ATTRIBUTE_BODY  => $resource->{Post::ATTRIBUTE_BODY},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        assert($resource instanceof Post);

        if (property_exists($resource, Post::LINK_AUTHOR) === true) {
            $authorDescription = [self::RELATIONSHIP_DATA => $resource->{Post::LINK_AUTHOR}];
        } else {
            $selfLink          = $this->getRelationshipSelfLink($resource, Post::LINK_AUTHOR);
            $authorDescription = [self::RELATIONSHIP_LINKS => [LinkInterface::SELF => $selfLink]];
        }

        if (property_exists($resource, Post::LINK_COMMENTS) === true) {
            $commentsDescription = [self::RELATIONSHIP_DATA => $resource->{Post::LINK_COMMENTS}];
        } else {
            $selfLink            = $this->getRelationshipSelfLink($resource, Post::LINK_COMMENTS);
            $commentsDescription = [self::RELATIONSHIP_LINKS => [LinkInterface::SELF => $selfLink]];
        }

        // NOTE: The `fixing` thing is for testing purposes only. Not for production.
        return $this->fixDescriptions(
            $resource,
            [
                Post::LINK_AUTHOR   => $authorDescription,
                Post::LINK_COMMENTS => $commentsDescription,
            ]
        );
    }
}
