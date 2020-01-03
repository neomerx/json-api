<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue91;

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

/**
 * @package Neomerx\Tests\JsonApi
 */
class CategorySchema extends BaseSchema
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'categories';
    }

    /**
     * @inheritdoc
     */
    public function getId($resource): ?string
    {
        assert($resource instanceof Category);

        return (string)$resource->index;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        /** @var Category $resource */
        return [
            'description' => $resource->description,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        /** @var Category $resource */
        return [
            'parent' => [self::RELATIONSHIP_DATA => $resource->parent],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return false;
    }
}
