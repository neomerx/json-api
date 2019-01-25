<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi\Schemas;

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
use Neomerx\Samples\JsonApi\Models\Author;

/**
 * @package Neomerx\Samples\JsonApi
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
        /** @var Author $author */
        return (string)$author->authorId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($author): iterable
    {
        /** @var Author $author */
        return [
            'first_name' => $author->firstName,
            'last_name'  => $author->lastName,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($resource): iterable
    {
        return [];
    }
}
