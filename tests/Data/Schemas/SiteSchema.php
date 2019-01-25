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
use Neomerx\Tests\JsonApi\Data\Models\Site;

/**
 * @package Neomerx\Tests\JsonApi
 */
class SiteSchema extends DevSchema
{
    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'sites';
    }

    /**
     * @inheritdoc
     */
    public function getId($resource): ?string
    {
        assert($resource instanceof Site);

        $index = $resource->{Site::ATTRIBUTE_ID};

        return $index === null ? $index : (string)$index;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource): iterable
    {
        assert($resource instanceof Site);

        return [
            Site::ATTRIBUTE_NAME => $resource->{Site::ATTRIBUTE_NAME},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource): iterable
    {
        assert($resource instanceof Site);

        if (property_exists($resource, Site::LINK_POSTS) === true) {
            $description = [self::RELATIONSHIP_DATA => $resource->{Site::LINK_POSTS}];
        } else {
            $selfLink    = $this->getRelationshipSelfLink($resource, Site::LINK_POSTS);
            $description = [self::RELATIONSHIP_LINKS => [LinkInterface::SELF => $selfLink]];
        }

        // NOTE: The `fixing` thing is for testing purposes only. Not for production.
        return $this->fixDescriptions(
            $resource,
            [
                Site::LINK_POSTS => $description,
            ]
        );
    }
}
