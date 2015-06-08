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
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Parameters\EncodingParameters;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncoderTest extends BaseTestCase
{
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

        $this->encoderOptions = new EncoderOptions(0, 'http://example.com');
    }

    /**
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfDuplicateObjectsWithAttributesOnly()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $endcoder->encode([$author, $author]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "links" : {
                        "self" : "http://example.com/people/9"
                    }
                },
                {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "links" : {
                        "self" : "http://example.com/people/9"
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
     * Test encode 2 duplicate resources with circular link to each other.
     */
    public function testEncodeDuplicatesWithCircularReferencesInData()
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');

        // That's gonna be a bit of a hack. We put author himself to author->comments link.
        // Don't be confused by the link name. It does not matter and I just don't want to create a new schema.
        $author->{Author::LINK_COMMENTS} = $author;

        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->setIncludePaths([]);
                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $endcoder->encode([$author, $author]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "relationships" : {
                        "comments" : {
                            "data" : { "type" : "people", "id" : "9" }
                        }
                    },
                    "links" : {
                        "self"     : "http://example.com/people/9"
                    }
                }, {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "relationships" : {
                        "comments" : {
                            "data" : { "type" : "people", "id" : "9" }
                        }
                    },
                    "links" : {
                        "self" : "http://example.com/people/9"
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
     * Test encode 2 main resource duplicates and try to apply field set filter on relations.
     */
    public function testEncodeDuplicatesWithRelationFieldSetFilter()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $comments = [
            Comment::instance(5, 'First!', $author),
            Comment::instance(12, 'I like XML better', $author),
        ];
        $endcoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions);

        $author->{Author::LINK_COMMENTS} = $comments;

        $actual = $endcoder->encode([$author, $author], null, null, new EncodingParameters(
            null,
            ['people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME]] // filter attributes
        ));

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "links" : {
                        "self" : "http://example.com/people/9"
                    }
                },
                {
                    "type"       : "people",
                    "id"         : "9",
                    "attributes" : {
                        "first_name" : "Dan",
                        "last_name"  : "Gebhardt"
                    },
                    "links" : {
                        "self" : "http://example.com/people/9"
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
     * Test encode simple resource object links.
     */
    public function testEncodeSimpleLinks()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
        ], $this->encoderOptions)->encode($this->getStandardPost());

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
                            { "type":"comments", "id":"5" },
                            { "type":"comments", "id":"12" }
                        ]
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
     * Test encode resource object links as references.
     */
    public function testEncodeEmptyLinks()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::DATA, null);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::DATA, []);
                return $schema;
            },
        ], $this->encoderOptions)->encode($this->getStandardPost());

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
                    "author"   : null,
                    "comments" : []
                },
                "links" : {
                    "self"     : "http://example.com/posts/1"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode with 'self' and 'related' URLs in main document and relationships.
     */
    public function testEncodeLinksInDocumentAndRelationships()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_SELF, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_RELATED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_SELF, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_RELATED, true);
                return $schema;
            },
        ], $this->encoderOptions)->encode($this->getStandardPost());

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
                        "data" : { "type" : "people", "id" : "9" },
                        "links" : {
                            "self"    : "http://example.com/posts/1/relationships/author",
                            "related" : "http://example.com/posts/1/author"
                        }
                    },
                    "comments" : {
                        "data":[
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ],
                        "links" : {
                            "self"    : "http://example.com/posts/1/relationships/comments",
                            "related" : "http://example.com/posts/1/comments"
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
     * Test encode with 'self' and 'related' URLs in main document and relationships.
     */
    public function testEncodeLinkWithMeta()
    {
        $comments = [
            Comment::instance(5, 'First!'),
            Comment::instance(12, 'I like XML better'),
        ];
        $author   = Author::instance(9, 'Dan', 'Gebhardt', $comments);
        $actual = Encoder::instance([
            Author::class  => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::SHOW_SELF, true);
                $schema->linkAddTo(
                    Author::LINK_COMMENTS,
                    AuthorSchema::LINKS,
                    [LinkInterface::SELF => new Link('relationships/comments', ['some' => 'meta'])]
                );
                return $schema;
            },
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions)->encode($author);

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
                    "comments"  : {
                        "data":[
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ],
                        "links" : {
                            "self" : {
                                "href" : "http://example.com/people/9/relationships/comments",
                                "meta" : { "some" : "meta" }
                            }
                        }
                    }
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
     * Test relationships meta.
     */
    public function testRelationshipsMeta()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->setRelationshipsMeta(['some' => 'meta']);
                return $schema;
            },
        ], $this->encoderOptions)->encode($this->getStandardPost());

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
                            { "type":"comments", "id":"5" },
                            { "type":"comments", "id":"12" }
                        ]
                    },
                    "meta" : {
                        "some" : "meta"
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
     * Test get encoder options.
     */
    public function testGetEncoderOptions()
    {
        $endoder = Encoder::instance([], $this->encoderOptions);
        $this->assertSame($this->encoderOptions, $endoder->getEncoderOptions());
    }

    /**
     * @return Post
     */
    private function getStandardPost()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $comments = [
            Comment::instance(5, 'First!'),
            Comment::instance(12, 'I like XML better'),
        ];
        $post     = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $author,
            $comments
        );

        return $post;
    }
}
