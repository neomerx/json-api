<?php namespace Neomerx\Tests\JsonApi\Encoder;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
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

use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Post;
use \Neomerx\Tests\JsonApi\Data\Site;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\Tests\JsonApi\Data\SiteSchema;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Http\Parameters\EncodingParameters;

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
     * @var EncoderOptions
     */
    private $encoderOptions;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->author   = Author::instance(9, 'Dan', 'Gebhardt');
        $this->comments = [
            Comment::instance(5, 'First!', $this->author),
            Comment::instance(12, 'I like XML better', $this->author),
        ];
        $this->post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            $this->comments
        );
        $this->site = Site::instance(2, 'site name', [$this->post]);
        $this->encoderOptions = new EncoderOptions(0, 'http://example.com');
    }

    /**
     * Test encode included objects.
     */
    public function testEncodeWithIncludedObjects()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => function ($factory, $container) {
                $schema = new CommentSchema($factory, $container);
                $schema->linkRemove(Comment::LINK_AUTHOR);
                return $schema;
            },
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->setIncludePaths([Post::LINK_COMMENTS]);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->post);

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode nested included objects with cyclic dependencies and sparse support.
     */
    public function testEncodeWithRecursiveIncludedObjects()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site, new EncodingParameters(
            // include only this relation (according to the spec intermediate will be included as well)
            [Site::LINK_POSTS . '.' . Post::LINK_COMMENTS],
            // include only these attributes and links
            [
                'comments' => [Comment::ATTRIBUTE_BODY, Comment::LINK_AUTHOR],
                'posts'    => [Post::LINK_COMMENTS],
                'sites'    => [Site::LINK_POSTS],
            ]
        ));

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
            }, {
                "type" : "posts",
                "id"   : "1",
                "relationships" : {
                    "comments" : {
                        "data" : [
                            { "type" : "comments", "id" : "5"  },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode included objects with null and empty links.
     */
    public function testEncodeWithNullAndEmptyLinks()
    {
        $this->post->{Post::LINK_AUTHOR}   = null;
        $this->post->{Post::LINK_COMMENTS} = [];

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site);

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode duplicate included objects with cyclic dependencies.
     */
    public function testEncodeDuplicatesWithCyclicDeps()
    {
        $this->post->{Post::LINK_COMMENTS} = [];

        // Note: Will use existing link (in schema) but set to 'wrong' type and let's see if it can handle it correctly.
        $this->post->{Post::LINK_AUTHOR} = $this->post;

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site);

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test link objects that should not be included but these objects link to others that should.
     * Parser should stop parsing even if deeper objects exist.
     */
    public function testEncodeLinkNonIncludableWithIncludableLinks()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => function ($factory, $container) {
                $schema = new SiteSchema($factory, $container);
                $schema->setIncludePaths([Site::LINK_POSTS]);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->site);

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode included objects.
     */
    public function testEncodeWithLinkWithPagination()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(
                    Post::LINK_COMMENTS,
                    PostSchema::LINKS,
                    [
                        Link::FIRST => function (PostSchema $schema, Post $post) {
                            return new Link($schema->getSelfSubUrl($post) . '/comments/first');
                        }
                    ]
                );
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->post);

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
                        "data" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ],
                        "links" : {
                            "first" : "http://example.com/posts/1/comments/first"
                        }
                    }
                },
                "links" : {
                    "self" : "http://example.com/posts/1"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode deep duplicate hierarchies.
     *
     * Test for issue 35
     */
    public function testEncodeDeepDuplicateHierarchies()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData([$this->site, $this->site]);

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
                "type" : "people",
                "id"   : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "relationships":{
                    "comments" : { "data":null }
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
            }, {
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
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode nested included objects for polymorphic arrays.
     */
    public function testEncodeWithIncludedForPolymorphicArrays()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData([$this->site, $this->author], new EncodingParameters([
            Site::LINK_POSTS . '.' . Post::LINK_AUTHOR,
            Author::LINK_COMMENTS,
        ]));

        $expected = <<<EOL
        {
            "data":[
                {
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
                },
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
                }
            ],
            "included":[
                {
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
                },
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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode relationship with polymorphic data.
     */
    public function testEncodePolymorphicRelationship()
    {
        // let's hack a little bit and place additional resource(s) into relationship
        $this->author->{Author::LINK_COMMENTS} = array_merge([$this->site], $this->comments);

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->author, new EncodingParameters([
            Author::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR,
        ]));

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
