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
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Encoder\EncodingParameters;

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
            // include only this relations
            [
                Site::LINK_POSTS,
                Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
            ],
            null // no filter for attributes
        ));

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
            },{
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
    public function testEncodeOnlyFieldSets()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site, new EncodingParameters(
            null,
            // include only these attributes and links
            [
                // note: no filter for 'comments'
                'people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME],
                'posts'  => [Post::LINK_COMMENTS, Post::LINK_AUTHOR],
                'sites'  => [Site::LINK_POSTS],
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
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
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
            }, {
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
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test that include and field-set parameters work without having to
     * explicitly add the values from include in the field set as well
     *
     * @access public
     * @return void
     */
    public function testIncludeAndSparseFieldSets()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site, new EncodingParameters(
            [
                Site::LINK_POSTS
            ],
            // include only these attributes and links
            [
                'posts' => [Post::ATTRIBUTE_TITLE, Post::ATTRIBUTE_BODY],
            ]
        ));

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test closures are not executed in lazy relationships.
     */
    public function testDataNotLoadedInLazyRelationships()
    {
        $throwExClosure = function () {
            throw new \Exception();
        };

        $actual = Encoder::instance([
            Author::class  => function ($factory, $container) use ($throwExClosure) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::DATA, $throwExClosure);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->author, new EncodingParameters(
            // do not include any relationships
            [],
            // include only these attributes (thus relationship that throws exception should not be invoked)
            [
                'people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME],
            ]
        ));

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encoder in mode when parser continues to parse even if relationship
     * are not in field-set (however child resources might be in 'include' paths).
     *
     * @see https://github.com/neomerx/json-api/issues/105
     */
    public function testIncludeAndSparseFieldSetsInGreedyMode()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = $this->createLoggedEncoder([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class,
        ], $this->encoderOptions)->encodeData($this->site, new EncodingParameters(
            [
                Site::LINK_POSTS,
                Site::LINK_POSTS . '.' . Post::LINK_AUTHOR,
                Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
                Site::LINK_POSTS . '.' . Post::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR,
                Site::LINK_POSTS . '.' . Post::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR . '.' . Author::LINK_COMMENTS,
            ],
            // include only these attributes and links
            [
                'sites'    => [],
                'posts'    => [],
                'comments' => [],
                'people'   => [Author::ATTRIBUTE_LAST_NAME, Author::LINK_COMMENTS],
            ]
        ));

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
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
