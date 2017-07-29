<?php namespace Neomerx\Tests\JsonApi\Encoder;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \ArrayIterator;
use \InvalidArgumentException;
use \Neomerx\JsonApi\Document\Link;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Post;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Contracts\Document\LinkInterface;
use \Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;

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
     * @expectedException \InvalidArgumentException
     */
    public function testEncodeInvalidData()
    {
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ], $this->encoderOptions);

        /** @noinspection PhpParamsInspection */
        $encoder->encodeData('input must be an object or array of objects or iterator over objects');
    }

    /**
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfDuplicateObjectsWithAttributesOnly()
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $encoder->encodeData([$author, $author]);

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

        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->setIncludePaths([]);
                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData([$author, $author]);

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
        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions);

        $author->{Author::LINK_COMMENTS} = $comments;

        $actual = $encoder->encodeData([$author, $author], new EncodingParameters(
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
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::DATA, null);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::DATA, []);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
                    "author"   : {"data" : null},
                    "comments" : {"data" : []}
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
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_SELF, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_RELATED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_SELF, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_RELATED, true);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
            Author::class  => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::SHOW_SELF, true);
                $schema->linkAddTo(
                    Author::LINK_COMMENTS,
                    AuthorSchema::LINKS,
                    [
                        LinkInterface::SELF => function (AuthorSchema $schema, Author $author) {
                            return new Link(
                                $schema->getSelfSubUrl($author) . '/relationships/comments',
                                ['some' => 'meta']
                            );
                        }
                    ]
                );
                return $schema;
            },
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions)->encodeData($author);

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
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->setRelationshipsMeta(['some' => 'meta']);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
     * Test add links to empty relationship.
     */
    public function testAddLinksToEmptyRelationship()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::DATA, null);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_RELATED, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::LINKS, ['foo' => new Link('/your/link', null, true)]);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::DATA, []);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::LINKS, [
                    'boo' => function (PostSchema $schema, Post $post) {
                        return new Link($schema->getSelfSubUrl($post) . '/another/link');
                    }
                ]);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
                        "links" : { "foo" : "/your/link", "related" : "http://example.com/posts/1/author" }
                    },
                    "comments" : {
                        "links" : { "boo" : "http://example.com/posts/1/another/link" }
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
     * Test add meta to empty relationship.
     */
    public function testAddMetaToEmptyRelationship()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::DATA, null);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::META, ['author' => 'meta']);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::DATA, []);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::META, function () {
                    return ['comments' => 'meta'];
                });
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
                       "meta": { "author": "meta" }
                    },
                    "comments" : {
                        "meta": { "comments": "meta" }
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
     * Test hide data section if it is omitted in Schema.
     */
    public function testHideDataSectionIfOmittedInSchema()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkRemoveFrom(Post::LINK_AUTHOR, PostSchema::DATA);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_RELATED, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::LINKS, ['foo' => new Link('/your/link', null, true)]);
                $schema->linkRemoveFrom(Post::LINK_COMMENTS, PostSchema::DATA);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::LINKS, [
                    'boo' => function (PostSchema $schema, Post $post) {
                        return new Link($schema->getSelfSubUrl($post) . '/another/link');
                    }
                ]);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
                        "links" : { "foo" : "/your/link", "related" : "http://example.com/posts/1/author" }
                    },
                    "comments" : {
                        "links" : { "boo" : "http://example.com/posts/1/another/link" }
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
     * Test closures are not executed in hidden relationships.
     */
    public function testDataNotLoadedInHiddenRelationships()
    {
        $throwExClosure = function () {
            throw new \Exception();
        };

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory) use ($throwExClosure) {
                $schema = new PostSchema($factory);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::DATA, $throwExClosure);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::SHOW_RELATED, true);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::LINKS, ['foo' => new Link('/your/link', null, true)]);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::DATA, $throwExClosure);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::SHOW_DATA, false);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::LINKS, [
                    'boo' => function (PostSchema $schema, Post $post) {
                        return new Link($schema->getSelfSubUrl($post) . '/another/link');
                    }
                ]);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($this->getStandardPost());

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
                        "links" : { "foo" : "/your/link", "related" : "http://example.com/posts/1/author" }
                    },
                    "comments" : {
                        "links" : { "boo" : "http://example.com/posts/1/another/link" }
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
     * Test encode Traversable (through Iterator) collection of resource(s).
     */
    public function testEncodeTraversableObjectsWithAttributesOnly()
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                //$schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            },
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions);

        // iterator here
        $author->{Author::LINK_COMMENTS} = new ArrayIterator([
            'comment1' => Comment::instance(5, 'First!'),
            'comment2' => Comment::instance(12, 'I like XML better'),
        ]);

        // and iterator here
        $itemSet = new ArrayIterator(['what_if_its_not_zero_based_array' => $author]);
        $actual  = $encoder->encodeData($itemSet);

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
                            "data" : [
                                { "type":"comments", "id":"5" },
                                { "type":"comments", "id":"12" }
                            ]
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
     * Test encode resource with single item array relationship.
     */
    public function testEncodeRelationshipWithSingleItem()
    {
        $post = Post::instance(1, 'Title', 'Body', null, [Comment::instance(5, 'First!')]);

        $actual = Encoder::instance([
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory) {
                $schema = new PostSchema($factory);
                $schema->linkRemove(Post::LINK_AUTHOR);
                return $schema;
            },
        ], $this->encoderOptions)->encodeData($post);

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "attributes" : {
                    "title" : "Title",
                    "body"  : "Body"
                },
                "relationships" : {
                    "comments" : {
                        "data" : [
                            { "type":"comments", "id":"5" }
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
     * Test encode with relationship self Link.
     */
    public function testEncodeWithRelationshipSelfLink()
    {
        $post   = $this->getStandardPost();
        $actual = Encoder::instance([
            Post::class => PostSchema::class,
        ])->withRelationshipSelfLink($post, Post::LINK_AUTHOR)->encodeData([]);

        $expected = <<<EOL
        {
            "links": {
                "self": "/posts/1/relationships/author"
            },
            "data" : []
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode with relationship related Link.
     */
    public function testEncodeWithRelationshipRelatedLink()
    {
        $post   = $this->getStandardPost();
        $actual = Encoder::instance([
            Post::class => PostSchema::class,
        ])->withRelationshipRelatedLink($post, Post::LINK_AUTHOR)->encodeData([]);

        $expected = <<<EOL
        {
            "links": {
                "related": "/posts/1/author"
            },
            "data" : []
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode unrecognized resource (no registered Schema).
     */
    public function testEncodeUnrecognizedResource()
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $post   = Post::instance(1, 'Title', 'Body', null, [Comment::instance(5, 'First!', $author)]);

        /** @var InvalidArgumentException $catch */
        $catch = null;
        try {
            Encoder::instance([
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
            ], $this->encoderOptions)->encodeData($post, new EncodingParameters([
                Post::LINK_COMMENTS
            ]));
        } catch (InvalidArgumentException $exception) {
            $catch = $exception;
        }

        $this->assertNotNull($catch);
        $this->assertContains(Post::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR, $catch->getMessage());
        $this->assertNotNull($catch->getPrevious());
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
