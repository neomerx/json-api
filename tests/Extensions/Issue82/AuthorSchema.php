<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue82;

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

use Neomerx\JsonApi\Schema\BaseSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AuthorSchema extends BaseSchema
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
    public function getId($author): ?string
    {
        return (string)$author->author_id;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($author): iterable
    {
        return [
            'first-name' => $author->first_name,
            'last-name'  => $author->last_name,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($author): iterable
    {
        return [
            'comments' => [
                self::RELATIONSHIP_LINKS_SELF    => false,
                self::RELATIONSHIP_LINKS_RELATED => true,

                // Data could be included as well
                // self::RELATIONSHIP_DATA => $author->comments,
            ],
        ];
    }
}
