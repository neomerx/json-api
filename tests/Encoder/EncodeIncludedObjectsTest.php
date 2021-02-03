<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Encoder;

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

use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Link;
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
class EncodeIncludedObjectsTest extends BaseTestCase
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
     * Test encode included objects.
     */
    public function testEncodeWithIncludedObjects(): void
    {
        $this->author->setIdentifierMeta('id meta');

        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->removeRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths(
            [Post::LINK_COMMENTS]
        )->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "posts",
                "id"   : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9", "meta": "id meta" }
                    },
                    "comments" : {
                        "data" : [
                            { "type":"comments", "id":"5" },
                            { "type":"comments", "id":"12" }
                        ]
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            },
            "included" : [{
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body"  : "First!"
                },
                "links" : {
                    "self" : "http://example.com/comments/5"
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "attributes" : {
                    "body"  : "I like XML better"
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
     * Test encode included objects, with relationship data returned as a generator.
     */
    public function testEncodeWithIncludedGenerator(): void
    {
        $this->post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            (function () { yield from $this->comments; })()
        );

        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->removeRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths(
            [Post::LINK_COMMENTS]
        )->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
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
                            { "type":"comments", "id":"5" },
                            { "type":"comments", "id":"12" }
                        ]
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            },
            "included" : [{
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body"  : "First!"
                },
                "links" : {
                    "self" : "http://example.com/comments/5"
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "attributes" : {
                    "body"  : "I like XML better"
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
     * Test encode included objects, with relationship data returned as a generator.
     *
     * @see https://github.com/neomerx/json-api/issues/252
     */
    public function testEncodeWithIncludedEmptyGenerator(): void
    {
        $this->post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            (function () { yield from []; })()
        );

        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->removeRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths(
            [Post::LINK_COMMENTS]
        )->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
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
                        "data" : []
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode nested included objects with cyclic dependencies and sparse support.
     */
    public function testEncodeWithRecursiveIncludedObjects(): void
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
                Site::class    => function ($factory) {
                    $schema = new SiteSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
                ]
            )->withFieldSets(
                [
                    // include only these attributes and links (note we specify relationships for linkage,
                    // otherwise those intermediate resources will not be included in the output)
                    'comments' => [Comment::ATTRIBUTE_BODY, Comment::LINK_AUTHOR],
                    'posts'    => [Post::LINK_COMMENTS],
                    'sites'    => [Site::LINK_POSTS],
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
                    "comments" : {
                        "data" : [
                            { "type" : "comments", "id" : "5"  },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                },
                "links": {
                    "self" : "http://example.com/posts/1"
                }
            }, {
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body"  : "First!"
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
                    "body"  : "I like XML better"
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
     * Test encode included objects with null and empty links.
     */
    public function testEncodeWithNullAndEmptyLinks(): void
    {
        $this->post->{Post::LINK_AUTHOR}   = null;
        $this->post->{Post::LINK_COMMENTS} = [];

        $actual = Encoder::instance(
            [
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
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths([Site::LINK_POSTS])->encodeData($this->site);

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
                    "author"   : {"data" : null},
                    "comments" : {"data" : []}
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode duplicate included objects with cyclic dependencies.
     */
    public function testEncodeDuplicatesWithCyclicDeps(): void
    {
        $this->post->{Post::LINK_COMMENTS} = [];

        // Note: Will use existing link (in schema) but set to 'wrong' type and let's see if it can handle it correctly.
        $this->post->{Post::LINK_AUTHOR} = $this->post;

        $actual = Encoder::instance(
            [
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
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths([Site::LINK_POSTS])->encodeData($this->site);

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
                    "author"   : {
                        "data" : { "type" : "posts", "id" : "1" }
                    },
                    "comments" : {"data" : []}
                }
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test link objects that should not be included but these objects link to others that should.
     * Parser should stop parsing even if deeper objects exist.
     */
    public function testEncodeLinkNonIncludableWithIncludableLinks(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
                Site::class    => function ($factory) {
                    $schema = new SiteSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths([Site::LINK_POSTS])
            ->encodeData($this->site);

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
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode included objects.
     */
    public function testEncodeWithLinkWithPagination(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideRelatedLinkInRelationship(Post::LINK_COMMENTS);
                    $schema->addToRelationship(
                        Post::LINK_COMMENTS,
                        PostSchema::RELATIONSHIP_LINKS,
                        [
                            Link::FIRST => function (PostSchema $schema, Post $post) {
                                return new Link(true, $schema->getSelfSubUrl($post) . '/comments/first', false);
                            },
                        ]
                    );
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "links" : {
                            "first" : "http://example.com/posts/1/comments/first",
                            "self"  : "http://example.com/posts/1/relationships/comments"
                        },
                        "data" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode deep duplicate hierarchies.
     *
     * Test for issue 35
     */
    public function testEncodeDeepDuplicateHierarchies(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => function ($factory) {
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
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
                Site::class    => function ($factory) {
                    $schema = new SiteSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS . '.' .
                    Comment::LINK_AUTHOR . '.' . Author::LINK_COMMENTS,
                ]
            )
            ->encodeData([$this->site, $this->site]);

        $expected = <<<EOL
        {
            "data" : [{
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
            }, {
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
            }],
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
                        "data" : { "type":"people", "id":"9" }
                    }
                },
                "links":{
                    "self" : "http://example.com/comments/5"
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
                "id"   : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships":{
                    "author" : {
                        "data" : { "type":"people", "id":"9" }
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
     * Test encode nested included objects for polymorphic arrays.
     */
    public function testEncodeWithIncludedForPolymorphicArrays(): void
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance(
            [
                Author::class  => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);
                    return $schema;
                },
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
                Site::class    => function ($factory) {
                    $schema = new SiteSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Site::LINK_POSTS . '.' . Post::LINK_AUTHOR,
                    Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
                    Author::LINK_COMMENTS,
                ]
            )
            ->encodeData([$this->author, $this->site]);

        $expected = <<<EOL
        {
            "data":[
                {
                    "type" : "people",
                    "id"   : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "relationships" : {
                        "comments" : {
                            "data" : [
                                { "type":"comments", "id":"5" },
                                { "type":"comments", "id":"12" }
                            ]
                        }
                    },
                    "links":{
                        "self":"http://example.com/people/9"
                    }
                }, {
                    "type" : "sites",
                    "id"   : "2",
                    "attributes" : {
                        "name" : "site name"
                    },
                    "relationships" : {
                        "posts" : {
                            "data" : [
                                { "type":"posts", "id":"1" }
                            ]
                        }
                    },
                    "links":{
                        "self" : "http://example.com/sites/2"
                    }
                }
            ],
            "included":[
                {
                    "type" : "comments",
                    "id"   : "5",
                    "attributes" : {
                        "body" : "First!"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type":"people", "id":"9" }
                        }
                    },
                    "links":{
                        "self":"http://example.com/comments/5"
                    }
                }, {
                    "type" : "comments",
                    "id"   : "12",
                    "attributes" : {
                        "body" : "I like XML better"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "9" }
                        }
                    },
                    "links" : {
                        "self" : "http://example.com/comments/12"
                    }
                }, {
                    "type" : "posts",
                    "id"   : "1",
                    "attributes" : {
                        "title" : "JSON API paints my bikeshed!",
                        "body"  : "Outside every fat man there was an even fatter man trying to close in"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : {
                                "type" : "people",
                                "id"   : "9"
                            }
                        },
                        "comments" : {
                            "data" : [
                                { "type":"comments", "id":"5" },
                                { "type":"comments", "id":"12" }
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
     * Test encode relationship with polymorphic data.
     */
    public function testEncodePolymorphicRelationship(): void
    {
        // let's hack a little bit and place additional resource(s) into relationship
        $this->author->{Author::LINK_COMMENTS} = array_merge([$this->site], $this->comments);

        $actual = Encoder::instance(
            [
                Author::class  => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);
                    return $schema;
                },
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
                Site::class    => function ($factory) {
                    $schema = new SiteSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Site::LINK_POSTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths(
                [
                    Author::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR,
                ]
            )
            ->encodeData($this->author);

        $expected = <<<EOL
        {
            "data":{
                "type" : "people",
                "id"   : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "relationships" : {
                    "comments" : {
                        "data" : [
                            { "type" : "sites",    "id" : "2" },
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                },
                "links":{
                    "self":"http://example.com/people/9"
                }
            },
            "included":[
                {
                    "type" : "sites",
                    "id"   : "2",
                    "attributes" : {
                        "name" : "site name"
                    },
                    "relationships" : {
                        "posts" : {
                            "data" : [
                                { "type" : "posts", "id" : "1" }
                            ]
                        }
                    }
                },
                {
                    "type" : "comments",
                    "id"   : "5",
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
                },
                {
                    "type" : "comments",
                    "id"   : "12",
                    "attributes" : {
                        "body" : "I like XML better"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "9" }
                        }
                    },
                    "links" : {
                        "self" : "http://example.com/comments/12"
                    }
                }
            ]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode relationships as links. Encoder do not follow include paths when links instead of data.
     *
     * @see https://github.com/neomerx/json-api/issues/121
     */
    public function testEncodeRelationshipsAsLinksDoNotFollowLinksWhenIncludePathSet(): void
    {
        unset($this->post->{Post::LINK_AUTHOR});
        unset($this->post->{Post::LINK_COMMENTS});

        $actual = Encoder::instance(
            [
                Post::class => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )
            ->withUrlPrefix('http://example.com')
            ->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "posts",
                "id"   : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "relationships" : {
                    "author" : {
                        "links" : {
                            "self" : "http://example.com/posts/1/relationships/author"
                        }
                    },
                    "comments" : {
                        "links" : {
                            "self" : "http://example.com/posts/1/relationships/comments"
                        }
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode relationships as links.
     *
     * @see https://github.com/neomerx/json-api/issues/121
     */
    public function testEncodeRelationshipsAsLinks(): void
    {
        unset($this->author->{Author::LINK_COMMENTS});

        $actual = Encoder::instance(
            [
                Author::class  => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->hideResourceLinks();
                    $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);
                    return $schema;
                },
                Comment::class => function ($factory) {
                    $schema = new CommentSchema($factory);
                    $schema->removeRelationship(Comment::LINK_AUTHOR);
                    return $schema;
                },
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->withIncludedPaths([Post::LINK_AUTHOR])->encodeData($this->post);

        $expected = <<<EOL
        {
            "data" : {
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
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            },
            "included": [
                {
                    "type" : "people",
                    "id"   : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "relationships" : {
                        "comments"  : {
                            "links" : {
                                "self" : "http://example.com/people/9/relationships/comments"
                            }
                        }
                    }
                }
            ]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test include paths as generator.
     *
     * @see https://github.com/neomerx/json-api/issues/238
     */
    public function testIterableParameterForWithIncludedPaths(): void
    {
        $author = Author::instance(238, 'Susan', 'Smith');
        $post   = Post::instance(11, 'Generators and Arrays', 'A tale of incompatible types', $author);

        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
            Post::class   => PostSchema::class,
        ]);

        $encoder = $encoder->withIncludedPaths($this->generateIncludeList());

        $json = $encoder->encodeData($post);

        self::assertJson($json);
    }

    /**
     * @return iterable
     */
    private function generateIncludeList(): iterable
    {
        foreach (['author'] as $item) {
            yield $item;
        }
    }
}
