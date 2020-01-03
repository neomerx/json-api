<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue236;

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

use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\Comment;

/**
 * @package Neomerx\Tests\JsonApi
 */
final class IssueTest extends BaseTestCase
{
    /**
     * Test having extra info (current path of the resource being parsed) in a schema.
     *
     * @see https://github.com/neomerx/json-api/issues/236
     */
    public function testDataSerializationWithPartialWildcard(): void
    {
        $pathsToInclude  = [Comment::LINK_AUTHOR];
        $fieldSetFilters = [];

        $actual = CustomEncoder::instance($this->prepareSchemas($pathsToInclude, $fieldSetFilters))
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths($pathsToInclude)
            ->encodeData($this->prepareDataToEncode());

        // please note the whole point of encoding is to have `include paths` and `current path` in meta so
        // the schema to know which relationships should be included.

        $expected = <<<EOL
        {
            "data" : {
                "type" : "comments",
                "id"   : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships" : {
                    "author" : {
                        "links" : {
                            "self": "http://example.com/comments/5/relationships/author"
                        },
                        "data"  : { "type":"people", "id":"42" },
                        "meta" : {
                            "current_path"             : "",
                            "fields_filter"            : null,
                            "relationships_to_include" : [
                                "author"
                            ]
                        }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/5"
                }
            },
            "included" : [{
                "type" : "people",
                "id"   : "42",
                "attributes" : {
                    "first_name" : "Peter",
                    "last_name"  : "Weller"
                },
                "relationships":{
                    "comments" : {
                        "links": {
                            "self": "http://example.com/people/42/relationships/comments"
                        },
                        "meta": {
                            "current_path"             : "author",
                            "fields_filter"            : null,
                            "relationships_to_include" : []
                        }
                    }
                },
                "links": {
                    "self": "http://example.com/people/42"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @return Comment
     */
    private function prepareDataToEncode(): Comment
    {
        $author  = Author::instance(42, 'Peter', 'Weller');
        $comment = Comment::instance(5, 'First!', $author);

        return $comment;
    }

    /**
     * @param iterable $expectedIncludePaths
     * @param iterable $expectedFieldSetFilters
     *
     * @return array
     */
    private function prepareSchemas(iterable $expectedIncludePaths, iterable $expectedFieldSetFilters): array
    {
        $schemaFields = new SchemaFields($expectedIncludePaths, $expectedFieldSetFilters);

        return [
            Author::class  => function ($factory) use ($expectedIncludePaths, $schemaFields) {
                return new CustomAuthorSchema($factory, $schemaFields);
            },
            Comment::class => function ($factory) use ($expectedIncludePaths, $schemaFields) {
                return new CustomCommentSchema($factory, $schemaFields);
            },
        ];
    }
}
