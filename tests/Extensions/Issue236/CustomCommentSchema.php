<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue236;

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
use Neomerx\Tests\JsonApi\Data\Models\Comment;

/**
 * @package Neomerx\Tests\JsonApi
 */
final class CustomCommentSchema extends BaseCustomSchema
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'comments';
    }

    /**
     * @inheritdoc
     */
    public function getId($resource): ?string
    {
        \assert($resource instanceof Comment);

        return (string)$resource->{Comment::ATTRIBUTE_ID};
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        \assert($resource instanceof Comment);

        return [
            Comment::ATTRIBUTE_BODY => $resource->{Comment::ATTRIBUTE_BODY},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNonHorrificRelationships($resource, string $currentPath): iterable
    {
        \assert($resource instanceof Comment);

        // The whole point of the custom schema is to demonstrate we have access to current path
        // of the resource being parsed and associated field filters and include paths.

        return [
            Comment::LINK_AUTHOR => [
                self::RELATIONSHIP_DATA          => $resource->{Comment::LINK_AUTHOR},
                self::RELATIONSHIP_LINKS_RELATED => false,
                self::RELATIONSHIP_META          => [
                    'current_path'             => $currentPath,
                    'fields_filter'            => $this->getSchemaFields()->getRequestedFields($this->getType()),
                    'relationships_to_include' => $this->getSchemaFields()->getRequestedRelationships($currentPath),
                ],
            ]
        ];
    }
}
