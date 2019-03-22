<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue231;

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

use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\Comment;
use Neomerx\Tests\JsonApi\Data\Models\Post;
use Neomerx\Tests\JsonApi\Data\Models\Site;
use Neomerx\Tests\JsonApi\Data\Schemas\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\CommentSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\PostSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\SiteSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{

    private function prepareSite(): Site
    {
        // prepare data to encode
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $commentAuthor  = Author::instance(42, 'Peter', 'Weller');

        $comments = [
            Comment::instance(5, 'First!', $commentAuthor),
            Comment::instance(12, 'I like XML better', $commentAuthor),
        ];

        $post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $author,
            $comments
        );
        return Site::instance(2, 'site name', [$post]);
    }

    private function prepareSchemas(): array
    {
        return [
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->hideResourceLinks();
                $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);
                return $schema;
            },
            Comment::class => function ($factory) {
                $schema = new CommentSchema($factory);
                $schema->hideDefaultLinksInRelationship(Comment::LINK_AUTHOR);
                return $schema;
            },
            Post::class => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->hideResourceLinks();
                $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                return $schema;
            },
            Site::class => function ($factory) {
                $schema = new SiteSchema($factory);
                $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                return $schema;
            }
        ];
    }

    /**
     * Test support of wildcards in include paths.
     *
     * @see https://github.com/neomerx/json-api/issues/231
     */
    public function testDataSerializationWithPartialWildcard(): void
    {
        // do encoding
        $actual = CustomEncoder::instance(
            $this->prepareSchemas()
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Site::LINK_POSTS
                        . DocumentInterface::PATH_SEPARATOR
                        . Post::LINK_COMMENTS
                        . DocumentInterface::PATH_SEPARATOR
                        . '*'
                ]
            )
            ->encodeData($this->prepareSite());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [{
                            "type" : "posts",
                            "id" : "1"
                        }]
                    }
                },
                "links" : {
                    "self" : "http://example.com/sites/2"
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author"   : { "data" : { "type" : "people", "id" : "9" } },
                    "comments" : { "data" : [
                        { "type" : "comments", "id" : "5" },
                        { "type" : "comments", "id" : "12" }
                    ]}
                }
            }, {
                "type" : "comments",
                "id"   : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type":"people", "id":"42" }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/5"
                }
            }, {
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
                        }
                    }
                }
            }, {
                "type" : "comments",
                "id"   : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships":{
                    "author" : {
                        "data" : { "type":"people", "id":"42" }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/12"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test support of wildcards in include paths.
     *
     * @see https://github.com/neomerx/json-api/issues/231
     */
    public function testDataSerializationWithFullWildcard(): void
    {
        // do encoding
        $actual = CustomEncoder::instance(
            $this->prepareSchemas()
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    '*'
                ]
            )
            ->encodeData($this->prepareSite());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [{
                            "type" : "posts",
                            "id" : "1"
                        }]
                    }
                },
                "links" : {
                    "self" : "http://example.com/sites/2"
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author"   : { "data" : { "type" : "people", "id" : "9" } },
                    "comments" : { "data" : [
                        { "type" : "comments", "id" : "5" },
                        { "type" : "comments", "id" : "12" }
                    ]}
                }
            }, {
                "type" : "people",
                "id"   : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "relationships":{
                    "comments" : {
                        "links": {
                            "self": "http://example.com/people/9/relationships/comments"
                        }
                    }
                }
            }, {
                "type" : "comments",
                "id"   : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type":"people", "id":"42" }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/5"
                }
            }, {
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
                        }
                    }
                }
            }, {
                "type" : "comments",
                "id"   : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships":{
                    "author" : {
                        "data" : { "type":"people", "id":"42" }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/12"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }
}
