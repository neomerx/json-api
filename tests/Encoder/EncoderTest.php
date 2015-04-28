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
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\Tests\JsonApi\Data\SiteSchema;
use \Neomerx\JsonApi\Document\DocumentLinks;
use \Neomerx\JsonApi\Encoder\EncodingOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Encoder\JsonEncodeOptions;
use \Neomerx\Tests\JsonApi\Data\PostSchemaEmptyLinks;
use \Neomerx\Tests\JsonApi\Data\AuthorSchemaWithComments;
use \Neomerx\Tests\JsonApi\Data\CommentSchemaWithAuthors;
use \Neomerx\Tests\JsonApi\Data\PostSchemaCommentsAsReference;
use \Neomerx\Tests\JsonApi\Data\PostSchemaWithCommentsIncluded;
use \Neomerx\Tests\JsonApi\Data\PostSchemaWithAuthorAndCommentsIncluded;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncoderTest extends BaseTestCase
{
    /**
     * Test encode simple object with attributes only.
     */
    public function testEncodeNull()
    {
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encode(null);

        $expected = <<<EOL
        {
            "data" : null
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object with attributes only.
     */
    public function testEncodeEmpty()
    {
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encode([]);

        $expected = <<<EOL
        {
            "data" : []
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object with attributes only.
     */
    public function testEncodeObjectWithAttributesOnly()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encode($author);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "9",
                "first_name" : "Dan",
                "last_name"  : "Gebhardt",
                "links" : {
                    "self" : "http://example.com/people/9"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object in pretty format.
     */
    public function testEncodeObjectWithAttributesOnlyPrettyPrinted()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ], new JsonEncodeOptions(JSON_PRETTY_PRINT));

        $actual = $endcoder->encode($author);

        $expected = <<<EOL
{
    "data": {
        "type": "people",
        "id": "9",
        "first_name": "Dan",
        "last_name": "Gebhardt",
        "links": {
            "self": "http:\/\/example.com\/people\/9"
        }
    }
}
EOL;
        // remove formatting from 'expected'
        //$expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfObjectsWithAttributesOnly()
    {
        $author1  = Author::instance(7, 'First', 'Last');
        $author2  = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encode([$author1, $author2]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "7",
                    "first_name" : "First",
                    "last_name"  : "Last",
                    "links" : {
                        "self" : "http://example.com/people/7"
                    }
                },
                {
                    "type"       : "people",
                    "id"         : "9",
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt",
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
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfDuplicateObjectsWithAttributesOnly()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encode([$author, $author]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "9",
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt",
                    "links" : {
                        "self" : "http://example.com/people/9"
                    }
                },
                {
                    "type"       : "people",
                    "id"         : "9",
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt",
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
     * Test encode meta and top-level links for simple object.
     */
    public function testEncodeMetaAndtopLinksForSimpleObject()
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $links  = new DocumentLinks('http://example.com/people/9');
        $meta   = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ];

        $actual = Encoder::instance([
            Author::class => AuthorSchema::class
        ])->encode($author, $links, $meta);

        $expected = <<<EOL
        {
            "meta" : {
                "copyright" : "Copyright 2015 Example Corp.",
                "authors" : [
                    "Yehuda Katz",
                    "Steve Klabnik",
                    "Dan Gebhardt"
                ]
            },
            "links" : {
                "self" : "http://example.com/people/9"
            },
            "data" : {
                "type"       : "people",
                "id"         : "9",
                "first_name" : "Dan",
                "last_name"  : "Gebhardt",
                "links" : {
                    "self" : "http://example.com/people/9"
                }
            }
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
        ])->encode($this->getStandardPost());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "title" : "JSON API paints my bikeshed!",
                "body"  : "Outside every fat man there was an even fatter man trying to close in",
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
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    // TODO add ref in included test

    /**
     * Test encode resource object links as references.
     */
    public function testEncodeLinkAsReference()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchemaCommentsAsReference::class,
        ])->encode($this->getStandardPost());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "title" : "JSON API paints my bikeshed!",
                "body"  : "Outside every fat man there was an even fatter man trying to close in",
                "links" : {
                    "self"     : "http://example.com/posts/1",
                    "author"   : "http://example.com/posts/1/author",
                    "comments" : "http://example.com/posts/1/comments"
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
            Post::class    => PostSchemaEmptyLinks::class,
        ])->encode($this->getStandardPost());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "title" : "JSON API paints my bikeshed!",
                "body"  : "Outside every fat man there was an even fatter man trying to close in",
                "links" : {
                    "self"     : "http://example.com/posts/1",
                    "author"   : null,
                    "comments" : []
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode included objects.
     */
    public function testEncodeWithIncludedObjects()
    {
        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchemaWithCommentsIncluded::class,
        ])->encode($this->getStandardPost());

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "posts",
                "id"    : "1",
                "title" : "JSON API paints my bikeshed!",
                "body"  : "Outside every fat man there was an even fatter man trying to close in",
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
                "body"  : "First!",
                "links" : {
                    "self" : "http://example.com/comments/5"
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "body"  : "I like XML better",
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
     * Test encode nested included objects with cyclic dependencies.
     *
     * @ign
     */
    public function testEncodeWithRecursiveIncludedObjects()
    {
        // TODO this test builds good hierarchy however due to lack of sparse support it doesn't test it

        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $comments = [
            Comment::instance(5, 'First!', $author),
            Comment::instance(12, 'I like XML better', $author),
        ];
        $author->{Author::LINK_COMMENTS} = $comments;
        $post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $author,
            $comments
        );
        $site = Site::instance(2, 'site name', [$post]);

        $actual = Encoder::instance([
            Author::class  => AuthorSchemaWithComments::class,
            Comment::class => CommentSchemaWithAuthors::class,
            Post::class    => PostSchemaWithAuthorAndCommentsIncluded::class,
            Site::class    => SiteSchema::class,
        ])->encode($site, null, null, new EncodingOptions(
            [Site::LINK_POSTS . '.' . Post::LINK_COMMENTS], // include only this relation
            ['comments' => [Comment::ATTRIBUTE_BODY]]       // filter attributes
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
                "body"  : "First!",
                "links" : {
                    "self"   : "http://example.com/comments/5",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    }
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "body"  : "I like XML better",
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

    // TODO add tests for duplicates (as links, as included, as links in included)
    // TODO add test for references in included
    // TODO add test for main resources duplicates with relation filter
    // TODO add circular dependency for two main resources. Will it handle it?

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
