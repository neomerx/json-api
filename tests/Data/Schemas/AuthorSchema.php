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

use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use function assert;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AuthorSchema extends DevSchema
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
        assert($resource instanceof Author);

        $index = $resource->{Author::ATTRIBUTE_ID};

        return $index === null ? $index : (string)$index;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource): iterable
    {
        assert($resource instanceof Author);

        return [
            Author::ATTRIBUTE_FIRST_NAME => $resource->{Author::ATTRIBUTE_FIRST_NAME},
            Author::ATTRIBUTE_LAST_NAME  => $resource->{Author::ATTRIBUTE_LAST_NAME},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource): iterable
    {
        assert($resource instanceof Author);

        // test and cover with test that factory could be used from a Schema.
        assert($this->getFactory()->createLink(true, 'test-example', false) !== null);

        if (property_exists($resource, Author::LINK_COMMENTS) === true) {
            $description = [self::RELATIONSHIP_DATA => $resource->{Author::LINK_COMMENTS}];
        } else {
            $selfLink    = $this->getRelationshipSelfLink($resource, Author::LINK_COMMENTS);
            $description = [self::RELATIONSHIP_LINKS => [LinkInterface::SELF => $selfLink]];
        }

        // NOTE: The `fixing` thing is for testing purposes only. Not for production.
        return $this->fixDescriptions(
            $resource,
            [
                Author::LINK_COMMENTS => $description,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function hasIdentifierMeta($resource): bool
    {
        assert($resource instanceof Author);

        return parent::hasIdentifierMeta($resource) || property_exists($resource, Author::IDENTIFIER_META);
    }

    /**
     * @inheritdoc
     */
    public function getIdentifierMeta($resource)
    {
        assert($resource instanceof Author);

        return $resource->{Author::IDENTIFIER_META};
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        assert($resource instanceof Author);

        return parent::hasResourceMeta($resource) || property_exists($resource, Author::RESOURCE_META);
    }

    /**
     * @inheritdoc
     */
    public function getResourceMeta($resource)
    {
        assert($resource instanceof Author);

        return $resource->{Author::RESOURCE_META};
    }
}
