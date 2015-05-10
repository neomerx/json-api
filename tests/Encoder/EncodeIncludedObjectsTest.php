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

use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Post;
use \Neomerx\Tests\JsonApi\Data\Site;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\Tests\JsonApi\Data\SiteSchema;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Parameters\EncodingParameters;

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
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, true);
                return $schema;
            },
        ])->encode($this->post);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "posts",
                "id"   : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "self" : "http://example.com/posts/1",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "linkage" : [
                            { "type":"comments", "id":"5" },
                            { "type":"comments", "id":"12" }
                        ]
                    }
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
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::INCLUDED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, true);
                return $schema;
            },
            Site::class    => SiteSchema::class,
        ])->encode($this->site, null, null, new EncodingParameters(
            // include only this relation
            [Site::LINK_POSTS . '.' . Post::LINK_COMMENTS],
            // include only these attributes and links
            [
                'comments' => [Comment::ATTRIBUTE_BODY, Comment::LINK_AUTHOR],
                'sites'    => [Site::LINK_POSTS],
            ]
        ));

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "comments",
                "id"    : "5",
                "attributes" : {
                    "body"  : "First!"
                },
                "links" : {
                    "self"   : "http://example.com/comments/5",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    }
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "attributes" : {
                    "body"  : "I like XML better"
                },
                "links" : {
                    "self"   : "http://example.com/comments/12",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
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
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::INCLUDED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, true);
                return $schema;
            },
            Site::class    => SiteSchema::class,
        ])->encode($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "author"   : null,
                    "comments" : []
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
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::INCLUDED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, true);
                return $schema;
            },
            Site::class    => SiteSchema::class,
        ])->encode($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "author"   : {
                        "linkage" : { "type" : "posts", "id" : "1" }
                    },
                    "comments" : []
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode included objects with reference links.
     */
    public function testEncodeLinksAsRefs()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_AS_REF, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::RELATED_CONTROLLER, 'author-controller-info');
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_AS_REF, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::RELATED_CONTROLLER, 'comments-controller-info');
                return $schema;
            },
            Site::class => SiteSchema::class,
        ])->encode($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "author"   : "http://example.com/posts/1/author",
                    "comments" : "http://example.com/posts/1/comments"
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
            Author::class  => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::INCLUDED, true);
                return $schema;
            },
            Comment::class => function ($factory, $container) {
                $schema = new CommentSchema($factory, $container);
                $schema->linkAddTo(Comment::LINK_AUTHOR, CommentSchema::INCLUDED, true);
                return $schema;
            },
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::INCLUDED, false);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, false);
                return $schema;
            },
            Site::class => SiteSchema::class,
        ])->encode($this->site);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "attributes" : {
                    "name"  : "site name"
                },
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "linkage" : [
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
            Comment::class => function ($factory, $container) {
                $schema = new CommentSchema($factory, $container);
                $schema->linkRemove(Comment::LINK_AUTHOR);
                return $schema;
            },
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(
                    Post::LINK_COMMENTS,
                    PostSchema::PAGINATION,
                    [PostSchema::PAGINATION_FIRST => '/first']
                );
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_PAGINATION, true);
                return $schema;
            },
        ])->encode($this->post);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "JSON API paints my bikeshed!",
                    "body"  : "Outside every fat man there was an even fatter man trying to close in"
                },
                "links" : {
                    "self" : "http://example.com/posts/1",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "linkage" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ],
                        "first" : "/first"
                    }
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
