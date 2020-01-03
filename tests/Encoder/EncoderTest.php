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

use ArrayIterator;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\Comment;
use Neomerx\Tests\JsonApi\Data\Models\Post;
use Neomerx\Tests\JsonApi\Data\Schemas\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\CommentSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\PostSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncoderTest extends BaseTestCase
{
    /**
     * Test encode invalid data.
     */
    public function testEncodeInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $encoder = Encoder::instance(
            [
                Author::class => AuthorSchema::class,
            ]
        )->withUrlPrefix('http://example.com');

        /** @noinspection PhpParamsInspection */
        $encoder->encodeData('input must be an object or array of objects or iterator over objects');
    }

    /**
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfDuplicateObjectsWithAttributesOnly(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance(
            [
                Author::class => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->removeRelationship(Author::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com');

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode 2 duplicate resources with circular link to each other.
     */
    public function testEncodeDuplicatesWithCircularReferencesInData(): void
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');

        // That's gonna be a bit of a hack. We put author himself to author->comments link.
        // Don't be confused by the link name. It does not matter and I just don't want to create a new schema.
        $author->{Author::LINK_COMMENTS} = $author;

        $encoder = Encoder::instance(
            [
                Author::class => AuthorSchema::class,
            ]
        )->withUrlPrefix('http://example.com');

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
                            "links": {
                                "self"    : "http://example.com/people/9/relationships/comments",
                                "related" : "http://example.com/people/9/comments"
                            },
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
                            "links": {
                                "self"    : "http://example.com/people/9/relationships/comments",
                                "related" : "http://example.com/people/9/comments"
                            },
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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode 2 main resource duplicates and try to apply field set filter on relations.
     */
    public function testEncodeDuplicatesWithRelationFieldSetFilter(): void
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $comments = [
            Comment::instance(5, 'First!', $author),
            Comment::instance(12, 'I like XML better', $author),
        ];
        $encoder  = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
            ]
        )->withUrlPrefix('http://example.com')->withFieldSets(
            // filter attributes
            ['people' => [Author::ATTRIBUTE_LAST_NAME, Author::ATTRIBUTE_FIRST_NAME]]
        );

        $author->{Author::LINK_COMMENTS} = $comments;

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode simple resource object links.
     */
    public function testEncodeSimpleLinks(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
                        "links": {
                            "self"    : "http://example.com/posts/1/relationships/author",
                            "related" : "http://example.com/posts/1/author"
                        },
                        "data" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "links": {
                            "self"    : "http://example.com/posts/1/relationships/comments",
                            "related" : "http://example.com/posts/1/comments"
                        },
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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode resource object links as references.
     */
    public function testEncodeEmptyLinks(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->addToRelationship(Post::LINK_AUTHOR, PostSchema::RELATIONSHIP_DATA, null);
                    $schema->addToRelationship(Post::LINK_COMMENTS, PostSchema::RELATIONSHIP_DATA, []);

                    // hide links
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);

                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode with 'self' and 'related' URLs in main document and relationships.
     */
    public function testEncodeLinksInDocumentAndRelationships(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
                            "self"    : "http://example.com/posts/1/relationships/author",
                            "related" : "http://example.com/posts/1/author"
                        },
                        "data" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "links" : {
                            "self"    : "http://example.com/posts/1/relationships/comments",
                            "related" : "http://example.com/posts/1/comments"
                        },
                        "data":[
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
     * Test encode with 'self' and 'related' URLs in main document and relationships.
     */
    public function testEncodeLinkWithMeta(): void
    {
        $comments = [
            Comment::instance(5, 'First!'),
            Comment::instance(12, 'I like XML better'),
        ];
        $author   = Author::instance(9, 'Dan', 'Gebhardt', $comments);
        $actual   = Encoder::instance(
            [
                Author::class  => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->hideRelatedLinkInRelationship(Author::LINK_COMMENTS);
                    $schema->addToRelationship(
                        Author::LINK_COMMENTS,
                        AuthorSchema::RELATIONSHIP_LINKS,
                        [
                            LinkInterface::SELF => function (AuthorSchema $schema, Author $author) {
                                return new Link(
                                    true,
                                    $schema->getSelfSubUrl($author) . '/relationships/comments',
                                    true,
                                    ['some' => 'meta']
                                );
                            },
                        ]
                    );
                    return $schema;
                },
                Comment::class => CommentSchema::class,
            ]
        )->withUrlPrefix('http://example.com')->encodeData($author);

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
                        "links" : {
                            "self" : {
                                "href" : "http://example.com/people/9/relationships/comments",
                                "meta" : { "some" : "meta" }
                            }
                        },
                        "data":[
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
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
     * Test add links to empty relationship.
     */
    public function testAddLinksToEmptyRelationship(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->removeFromRelationship(Post::LINK_AUTHOR, PostSchema::RELATIONSHIP_DATA);
                    $selfLink    = new Link(false, 'http://foo.boo/custom-self', false);
                    $relatedLink = new Link(false, 'http://foo.boo/custom-related', false);
                    $schema->setSelfLinkInRelationship(Post::LINK_AUTHOR, $selfLink);
                    $schema->setRelatedLinkInRelationship(Post::LINK_AUTHOR, $relatedLink);
                    $schema->addToRelationship(
                        Post::LINK_AUTHOR,
                        PostSchema::RELATIONSHIP_LINKS,
                        ['foo' => new Link(false, '/your/link', false)]
                    );
                    $schema->removeFromRelationship(Post::LINK_COMMENTS, PostSchema::RELATIONSHIP_DATA);
                    $schema->addToRelationship(
                        Post::LINK_COMMENTS,
                        PostSchema::RELATIONSHIP_LINKS,
                        [
                            'boo' => function (PostSchema $schema, Post $post) {
                                return new Link(true, $schema->getSelfSubUrl($post) . '/another/link', false);
                            },
                        ]
                    );
                    $schema->hideRelatedLinkInRelationship(Post::LINK_COMMENTS);

                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
                        "links" : {
                            "self"    : "http://foo.boo/custom-self",
                            "related" : "http://foo.boo/custom-related",
                            "foo"     : "/your/link"
                        }
                    },
                    "comments" : {
                        "links" : {
                            "boo"  : "http://example.com/posts/1/another/link",
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
     * Test add meta to empty relationship.
     */
    public function testAddMetaToEmptyRelationship(): void
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->removeFromRelationship(Post::LINK_AUTHOR, PostSchema::RELATIONSHIP_DATA);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_AUTHOR);
                    $schema->addToRelationship(Post::LINK_AUTHOR, PostSchema::RELATIONSHIP_META, ['author' => 'meta']);
                    $schema->removeFromRelationship(Post::LINK_COMMENTS, PostSchema::RELATIONSHIP_DATA);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    $schema->addToRelationship(
                        Post::LINK_COMMENTS,
                        PostSchema::RELATIONSHIP_META,
                        function () {
                            return ['comments' => 'meta'];
                        }
                    );
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test hide data section if it is omitted in Schema.
     */
    public function testHideDataSectionIfOmittedInSchema()
    {
        $actual = Encoder::instance(
            [
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->removeFromRelationship(Post::LINK_AUTHOR, PostSchema::RELATIONSHIP_DATA);
                    $schema->hideSelfLinkInRelationship(Post::LINK_AUTHOR);
                    $schema->addToRelationship(
                        Post::LINK_AUTHOR,
                        PostSchema::RELATIONSHIP_LINKS,
                        ['foo' => new Link(false, '/your/link', false)]
                    );
                    $schema->removeFromRelationship(Post::LINK_COMMENTS, PostSchema::RELATIONSHIP_DATA);
                    $schema->addToRelationship(
                        Post::LINK_COMMENTS,
                        PostSchema::RELATIONSHIP_LINKS,
                        [
                            'boo' => function (PostSchema $schema, Post $post) {
                                return new Link(true, $schema->getSelfSubUrl($post) . '/another/link', false);
                            },
                        ]
                    );
                    $schema->hideRelatedLinkInRelationship(Post::LINK_COMMENTS);

                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($this->getStandardPost());

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
                        "links" : {
                            "boo"  : "http://example.com/posts/1/another/link",
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
     * Test encode Traversable (through Iterator) collection of resource(s).
     */
    public function testEncodeTraversableObjectsWithAttributesOnly(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance(
            [
                Author::class  => function ($factory) {
                    $schema = new AuthorSchema($factory);
                    $schema->hideDefaultLinksInRelationship(Author::LINK_COMMENTS);
                    return $schema;
                },
                Comment::class => CommentSchema::class,
            ]
        )->withUrlPrefix('http://example.com');

        // iterator here
        $author->{Author::LINK_COMMENTS} = new ArrayIterator(
            [
                'comment1' => Comment::instance(5, 'First!'),
                'comment2' => Comment::instance(12, 'I like XML better'),
            ]
        );

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode resource with single item array relationship.
     */
    public function testEncodeRelationshipWithSingleItem(): void
    {
        $post = Post::instance(1, 'Title', 'Body', null, [Comment::instance(5, 'First!')]);

        $actual = Encoder::instance(
            [
                Comment::class => CommentSchema::class,
                Post::class    => function ($factory) {
                    $schema = new PostSchema($factory);
                    $schema->removeRelationship(Post::LINK_AUTHOR);
                    $schema->hideDefaultLinksInRelationship(Post::LINK_COMMENTS);
                    return $schema;
                },
            ]
        )->withUrlPrefix('http://example.com')->encodeData($post);

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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode with relationship self Link.
     */
    public function testEncodeWithRelationshipSelfLink(): void
    {
        $post   = $this->getStandardPost();
        $actual = Encoder::instance(
            [
                Post::class => PostSchema::class,
            ]
        )->withRelationshipSelfLink($post, Post::LINK_AUTHOR)->encodeData([]);

        $expected = <<<EOL
        {
            "links": {
                "self": "/posts/1/relationships/author"
            },
            "data" : []
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode with relationship related Link.
     */
    public function testEncodeWithRelationshipRelatedLink(): void
    {
        $post   = $this->getStandardPost();
        $actual = Encoder::instance(
            [
                Post::class => PostSchema::class,
            ]
        )->withRelationshipRelatedLink($post, Post::LINK_AUTHOR)->encodeData([]);

        $expected = <<<EOL
        {
            "links": {
                "related": "/posts/1/author"
            },
            "data" : []
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode unrecognized resource (no registered Schema).
     */
    public function testEncodeUnrecognizedResourceAtRoot(): void
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');

        /** @var InvalidArgumentException $catch */
        $catch = null;
        try {
            Encoder::instance(
                [
                    Post::class => PostSchema::class,
                ]
            )->withUrlPrefix('http://example.com')->encodeData($author);
        } catch (InvalidArgumentException $exception) {
            $catch = $exception;
        }

        self::assertNotNull($catch);
        self::assertStringContainsString('top-level', $catch->getMessage());
    }

    /**
     * Test encode unrecognized resource (no registered Schema).
     */
    public function testEncodeUnrecognizedResourceInRelationship(): void
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $post   = Post::instance(1, 'Title', 'Body', null, [Comment::instance(5, 'First!', $author)]);

        /** @var InvalidArgumentException $catch */
        $catch = null;
        try {
            Encoder::instance(
                [
                    Comment::class => CommentSchema::class,
                    Post::class    => PostSchema::class,
                ]
            )->withUrlPrefix('http://example.com')->withIncludedPaths(
                [
                    Post::LINK_COMMENTS,
                ]
            )->encodeData($post);
        } catch (InvalidArgumentException $exception) {
            $catch = $exception;
        }

        self::assertNotNull($catch);
        self::assertStringContainsString(Post::LINK_COMMENTS . '.' . Comment::LINK_AUTHOR, $catch->getMessage());
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
