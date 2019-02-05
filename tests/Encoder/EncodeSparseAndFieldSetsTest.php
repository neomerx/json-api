<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Encoder;

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

use Exception;
use Neomerx\JsonApi\Encoder\Encoder;
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
class EncodeSparseAndFieldSetsTest extends BaseTestCase
{
    /**
     * @var Author
     */
    private $author;

    /**
     * @var Comment[]
     */
    private $comments;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var Site
     */
    private $site;

    /**
     * Set up.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->author   = Author::instance(9, 'Dan', 'Gebhardt');
        $this->comments = [
            Comment::instance(5, 'First!', $this->author),
            Comment::instance(12, 'I like XML better', $this->author),
        ];
        $this->post     = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            $this->comments
        );
        $this->site     = Site::instance(2, 'site name', [$this->post]);
    }

    /**
     * Test encode nested included objects with cyclic dependencies and sparse support.
     */
    public function testEncodeWithRecursiveIncludedObjects(): void
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance($this->getSchemasWithoutDefaultLinksInRelationships())
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    // include only this relations
                    Site::LINK_POSTS,
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
                ]
            )->encodeData($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "2",
                "attributes" : {
                    "name" : "site name"
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
                "type" : "posts",
                "id"   : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "data" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                }
            }, {
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    }
                },
                "links" : {
                    "self"   : "http://example.com/comments/5"
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    }
                },
                "links" : {
                    "self"   : "http://example.com/comments/12"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode nested included objects with cyclic dependencies and sparse support.
     */
    public function testEncodeOnlyFieldSets(): void
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance($this->getSchemasWithoutDefaultLinksInRelationships())
            ->withUrlPrefix('http://example.com')->withFieldSets(
                [
                    // note: no filter for 'comments'
                    'people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME],
                    'posts'  => [Post::LINK_COMMENTS, Post::LINK_AUTHOR],
                    'sites'  => [Site::LINK_POSTS],
                ]
            )->withIncludedPaths(
                [
                    Site::LINK_POSTS,
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR,
                ]
            )->encodeData($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
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
                "type" : "posts",
                "id"   : "1",
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "data" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                }
            }, {
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    }
                },
                "links" : {
                    "self" : "http://example.com/comments/5"
                }
            }, {
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships" : {
                    "author" : {
                        "data" : {
                            "type" : "people",
                            "id"   : "9"
                        }
                    }
                },
                "links" : {
                    "self" : "http://example.com/comments/12"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test that include and field-set parameters work without having to
     * explicitly add the values from include in the field set as well
     *
     * @access public
     * @return void
     */
    public function testIncludeAndSparseFieldSets(): void
    {
        $actual = Encoder::instance($this->getSchemasWithoutDefaultLinksInRelationships())
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Site::LINK_POSTS,
                ]
            )->withFieldSets(
                [
                    'posts' => [Post::ATTRIBUTE_TITLE, Post::ATTRIBUTE_BODY],
                ]
            )->encodeData($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "2",
                "attributes": {
                    "name": "site name"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [
                            { "type" : "posts", "id" : "1" }
                        ]
                    }
                },
                "links" : {
                    "self" : "http://example.com/sites/2"
                }
            },
            "included" : [
            {
                "type" : "posts",
                "id"   : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test meta closures are not executed in lazy relationships.
     */
    public function testMetaNotLoadedInLazyRelationships(): void
    {
        $throwExClosure = function () {
            throw new Exception();
        };

        $actual = Encoder::instance(
            [
                Author::class => function ($factory) use ($throwExClosure) {
                    $schema = new AuthorSchema($factory);
                    $schema->addToRelationship(Author::LINK_COMMENTS, AuthorSchema::RELATIONSHIP_META, $throwExClosure);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->withFieldSets(
            [
                // include only these attributes (thus relationship that throws exception should not be invoked)
                'people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME],
            ]
        )->encodeData($this->author);

        $expected = <<<EOL
        {
            "data":{
                "type" : "people",
                "id"   : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "links":{
                    "self":"http://example.com/people/9"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encoder in mode when parser continues to parse even if relationship
     * are not in field-set (however child resources might be in 'include' paths).
     *
     * @see https://github.com/neomerx/json-api/issues/105
     */
    public function testIncludeAndSparseFieldSetsInGreedyMode(): void
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance($this->getSchemasWithoutDefaultLinksInRelationships())
            ->withUrlPrefix('http://example.com')->withIncludedPaths(
                [
                    Site::LINK_POSTS . '.' . Post::LINK_AUTHOR,
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS . '.' .
                    Comment::LINK_AUTHOR . '.' . Author::LINK_COMMENTS,
                ]
            )->withFieldSets(
                [
                    // include only these attributes and links
                    'sites'    => [],                  // note relationship resources will NOT be in included
                    'posts'    => [Post::LINK_AUTHOR], // note relationship resources will be in included
                    'comments' => [],                  // note relationship resources will NOT be in included
                    'people'   => [Author::ATTRIBUTE_LAST_NAME, Author::LINK_COMMENTS],
                ]
            )->encodeData($this->site);

        $expected = <<<EOL
        {
            "data":{
                "type" : "sites",
                "id"   : "2",
                "links" : {
                    "self" : "http://example.com/sites/2"
                }
            },
            "included" : [
                {
                    "type" : "people",
                    "id"   : "9",
                    "attributes" : {
                        "last_name" : "Gebhardt"
                    },
                    "relationships" : {
                        "comments" : {
                            "data" : [
                                { "type" : "comments", "id" : "5"  },
                                { "type" : "comments", "id" : "12" }
                            ]
                        }
                    }
                }
            ]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @return array
     */
    private function getSchemasWithoutDefaultLinksInRelationships(): array
    {
        $authorSchema  = function ($factory) {
            $schema = new AuthorSchema($factory);
            $schema->hideResourceLinks();
            $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);

            return $schema;
        };
        $commentSchema = function ($factory) {
            $schema = new CommentSchema($factory);
            $schema->hideDefaultLinksInRelationship(Comment::LINK_AUTHOR);

            return $schema;
        };
        $postSchema    = function ($factory) {
            $schema = new PostSchema($factory);
            $schema->hideResourceLinks();
            $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
            $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);

            return $schema;
        };
        $siteSchema    = function ($factory) {
            $schema = new SiteSchema($factory);
            $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);

            return $schema;
        };

        return [
            Author::class  => $authorSchema,
            Comment::class => $commentSchema,
            Post::class    => $postSchema,
            Site::class    => $siteSchema,
        ];
    }
}
