<?php namespace Neomerx\Tests\JsonApi\Encoder;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Author;
use Neomerx\Tests\JsonApi\Data\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Collection;
use Neomerx\Tests\JsonApi\Data\Comment;
use Neomerx\Tests\JsonApi\Data\CommentSchema;
use Neomerx\Tests\JsonApi\Data\Site;
use Neomerx\Tests\JsonApi\Data\SiteSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeSimpleObjectsTest extends BaseTestCase
{
    /**
     * @var EncoderOptions
     */
    private $encoderOptions;

    protected function setUp()
    {
        parent::setUp();

        $this->encoderOptions = new EncoderOptions(0, 'http://example.com');
    }

    /**
     * Test encode null.
     */
    public function testEncodeNull(): void
    {
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ]);

        $actual = $encoder->encodeData(null);

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
     * Test encode empty array.
     */
    public function testEncodeEmpty(): void
    {
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ]);

        $actual = $encoder->encodeData([]);

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
     * Test encode empty iterator.
     */
    public function testEncodeEmptyIterator(): void
    {
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ]);

        $actual = $encoder->encodeData(new \ArrayIterator([]));

        $expected = <<<EOL
        {
            "data": []
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode empty array.
     *
     * Issue #50 @link https://github.com/neomerx/json-api/issues/50
     */
    public function testEncodeEmptyWithParameters(): void
    {
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ]);

        $actual = $encoder->encodeData([], new EncodingParameters(null, [
            // include only these attributes and links
            'authors' => [Author::ATTRIBUTE_FIRST_NAME, Author::LINK_COMMENTS],
        ]));

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
    public function testEncodeObjectWithAttributesOnly(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
        {
            "data" : {
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
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object without ID and attributes only.
     */
    public function testEncodeObjectWithAttributesOnlyAndNoId(): void
    {
        $author                         = Author::instance(9, 'Dan', 'Gebhardt');
        $author->{Author::ATTRIBUTE_ID} = null;
        $encoder                        = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);
                $schema->setResourceLinksClosure(function () {
                    return []; // no `self` link and others
                });

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object with attributes and custom links.
     *
     * @see https://github.com/neomerx/json-api/issues/64
     */
    public function testEncodeObjectWithAttributesAndCustomLinks(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);
                $schema->setResourceLinksClosure(function ($resource) {
                    $this->assertNotNull($resource);

                    return [
                        'custom' => new Link('http://custom-link.com/', null, true),
                    ];
                });

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "links" : {
                    "custom" : "http://custom-link.com/"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object as resource identity.
     */
    public function testEncodeObjectAsResourceIdentity(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ], $this->encoderOptions);

        $actual = $encoder->encodeIdentifiers($author);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "people",
                "id"   : "9"
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode array of simple objects as resource identity.
     */
    public function testEncodeArrayAsResourceIdentity(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ], $this->encoderOptions);

        $actual = $encoder->encodeIdentifiers([$author]);

        $expected = <<<EOL
        {
            "data" : [{
                "type" : "people",
                "id"   : "9"
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object with attributes only in array.
     */
    public function testEncodeObjectWithAttributesOnlyInArray(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData([$author]);

        $expected = <<<EOL
        {
            "data" : [{
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "links" : {
                    "self" : "http://example.com/people/9"
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object with attributes only associative array.
     */
    public function testEncodeObjectWithAttributesOnlyInAssocArray(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData(['key_doesnt_matter' => $author]);

        $expected = <<<EOL
        {
            "data" : [{
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "links" : {
                    "self" : "http://example.com/people/9"
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode simple object in pretty format.
     */
    public function testEncodeObjectWithAttributesOnlyPrettyPrinted(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
{
    "data": {
        "type": "people",
        "id": "9",
        "attributes": {
            "first_name": "Dan",
            "last_name": "Gebhardt"
        },
        "links": {
            "self": "http:\/\/example.com\/people\/9"
        }
    }
}
EOL;

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode array of simple objects with attributes only.
     */
    public function testEncodeArrayOfObjectsWithAttributesOnly(): void
    {
        $author1 = Author::instance(7, 'First', 'Last');
        $author2 = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions);

        $actual = $encoder->encodeData([$author1, $author2]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "7",
                    "attributes" : {
                        "first_name" : "First",
                        "last_name"  : "Last"
                    },
                    "links" : {
                        "self" : "http://example.com/people/7"
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
     * Test encode meta and top-level links for simple object.
     */
    public function testEncodeMetaAndtopLinksForSimpleObject(): void
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $links  = [Link::SELF => new Link('/people/9')];
        $meta   = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt",
            ],
        ];

        $actual = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions)->withLinks($links)->withMeta($meta)->encodeData($author);

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
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
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
     * Test encode meta.
     */
    public function testEncodeMeta(): void
    {
        $meta = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt",
            ],
        ];

        $actual = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ])->encodeMeta($meta);

        $expected = <<<EOL
        {
            "meta" : {
                "copyright" : "Copyright 2015 Example Corp.",
                "authors" : [
                    "Yehuda Katz",
                    "Steve Klabnik",
                    "Dan Gebhardt"
                ]
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encoding with JSON API version.
     */
    public function testEncodeJsonApiVersion(): void
    {
        $actual = Encoder::instance()->withJsonApiVersion(['some' => 'meta'])->encodeData(null);

        $expected = <<<EOL
        {
            "jsonapi" : {
                "version" : "1.0",
                "meta"    : { "some" : "meta" }
            },
            "data" : null
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode polymorphic array (resources of different types).
     */
    public function testEncodePolymorphicArray(): void
    {
        $author  = Author::instance(7, 'First', 'Last', []);
        $site    = Site::instance(9, 'Main Site', []);
        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
            Site::class   => SiteSchema::class,
        ], $this->encoderOptions);

        $actual = $encoder->encodeData([$author, $site]);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type" : "people",
                    "id"   : "7",
                    "attributes" : {
                        "first_name" : "First",
                        "last_name"  : "Last"
                    },
                    "relationships" : {
                        "comments" : {
                            "data" : []
                        }
                    },
                    "links" : {
                        "self":"http://example.com/people/7"
                    }
                }, {
                    "type" : "sites",
                    "id"   : "9",
                    "attributes" : {
                        "name" : "Main Site"
                    },
                    "relationships" : {
                        "posts" : {
                            "data" : []
                        }
                    },
                    "links" : {
                        "self":"http://example.com/sites/9"
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
     * Test encode simple object with attributes only in ArrayAccess collection.
     */
    public function testEncodeObjectWithAttributesOnlyInArrayAccessCollection(): void
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);

                return $schema;
            },
        ], $this->encoderOptions);

        $collection   = new Collection();
        $collection[] = $author;

        $actual = $encoder->encodeData($collection);

        $expected = <<<EOL
        {
            "data" : [{
                "type"       : "people",
                "id"         : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "links" : {
                    "self" : "http://example.com/people/9"
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode with Schema instance.
     *
     * @link https://github.com/neomerx/json-api/issues/168
     */
    public function testEncodeWithSchmaInstance(): void
    {
        $authorSchema = new AuthorSchema(new Factory());
        $authorSchema->linkRemove(Author::LINK_COMMENTS);

        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = Encoder::instance([
            Author::class => $authorSchema,
        ], $this->encoderOptions);

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
        {
            "data" : {
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
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode for 2 relationships that have identical name beginnings.
     *
     * @return void
     *
     * @link https://github.com/neomerx/json-api/issues/200
     */
    public function testEncodingSimilarRelationships(): void
    {
        /**
         * It's odd to have a second comments relationship and the naming is also weird but...
         * we need a second relationship that which naming start identical to the first one.
         */
        $secondRelName = Author::LINK_COMMENTS . '-second-name';

        $comment1 = Comment::instance(1, 'One!');
        $comment5 = Comment::instance(5, 'Five!');
        $author  = Author::instance(9, 'Dan', 'Gebhardt', [$comment1]);

        $actual = Encoder::instance([
            Author::class  => function ($factory) use ($secondRelName, $comment5) {
                $schema = new AuthorSchema($factory);

                // make the author have the comment only in that odd relationship
                // we will emulate the new relationship with that comment
                $schema->linkAddTo($secondRelName, AuthorSchema::DATA, function () use ($comment5) {
                    return [$comment5];
                });

                return $schema;
            },
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions)->encodeData($author, new EncodingParameters(
            // include only the new odd relationship and omit the original `comments` relationship
            [$secondRelName]
        ));

        // The issue was that comment with id 1 was also added in `included` section
        $expected = <<<EOL
        {
            "data": {
                "type": "people",
                "id": "9",
                "attributes": {
                    "first_name": "Dan",
                    "last_name": "Gebhardt"
                },
                "relationships": {
                    "comments": {
                    "data": [
                        {
                            "type": "comments",
                            "id": "1"
                        }
                    ]
                    },
                    "comments-second-name": {
                        "data": [
                            {
                                "type": "comments",
                                "id": "5"
                            }
                        ]
                    }
                },
                "links": {
                    "self": "http://example.com/people/9"
                }
            },
            "included": [
                {
                    "type": "comments",
                    "id": "5",
                    "attributes": {
                    "body": "Five!"
                    },
                    "relationships": {
                        "author": { "data": null }
                    },
                    "links": {
                        "self": "http://example.com/comments/5"
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
