<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue81;

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

use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\Comment;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test encoder will encode identities.
     *
     * @see https://github.com/neomerx/json-api/issues/81
     */
    public function testEnheritedEncoder(): void
    {
        $author                          = Author::instance(321, 'John', 'Dow')->setIdentifierMeta('id meta');
        $comment                         = Comment::instance(123, 'Comment body', $author);
        $author->{Author::LINK_COMMENTS} = [$comment];

        $actual = Encoder::instance(
            [
                Comment::class => CommentSchema::class,
            ]
        )->encodeData($comment);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "comments",
                "id"         : "123",
                "attributes" : {
                    "body" : "Comment body"
                },
                "relationships" : {
                    "author" : {
                        "links": {
                            "self"    : "/comments/123/relationships/author",
                            "related" : "/comments/123/author"
                        },
                        "data" : { "type" : "people", "id" : "321", "meta": "id meta" }
                    }
                },
                "links" : {
                    "self" : "/comments/123"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }
}
