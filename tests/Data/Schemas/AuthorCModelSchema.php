<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Schemas;

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
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\Tests\JsonApi\Data\Models\AuthorCModel;
use function array_key_exists;
use function assert;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AuthorCModelSchema extends DevSchema
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'people';
    }

    /**
     * @inheritdoc
     */
    public function getId($resource): ?string
    {
        assert($resource instanceof AuthorCModel);

        $index = $resource[AuthorCModel::ATTRIBUTE_ID];

        return $index === null ? $index : (string)$index;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        assert($resource instanceof AuthorCModel);

        return [
            AuthorCModel::ATTRIBUTE_FIRST_NAME => $resource[AuthorCModel::ATTRIBUTE_FIRST_NAME],
            AuthorCModel::ATTRIBUTE_LAST_NAME  => $resource[AuthorCModel::ATTRIBUTE_LAST_NAME],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        assert($resource instanceof AuthorCModel);

        if (array_key_exists(AuthorCModel::LINK_COMMENTS, (array)$resource) === true) {
            $description = [self::RELATIONSHIP_DATA => $resource[AuthorCModel::LINK_COMMENTS]];
        } else {
            $selfLink    = $this->getRelationshipSelfLink($resource, AuthorCModel::LINK_COMMENTS);
            $description = [self::RELATIONSHIP_LINKS => [LinkInterface::SELF => $selfLink]];
        }

        // NOTE: The `fixing` thing is for testing purposes only. Not for production.
        return $this->fixDescriptions(
            $resource,
            [
                AuthorCModel::LINK_COMMENTS => $description,
            ]
        );
    }
}
