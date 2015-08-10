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
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\JsonApi\Parameters\EncodingParameters;

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
    public function testEncodeNull()
    {
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encodeData(null);

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
    public function testEncodeEmpty()
    {
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encodeData([]);

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
    public function testEncodeEmptyIterator()
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
    public function testEncodeEmptyWithParameters()
    {
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->encodeData([], new EncodingParameters(null, [
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
    public function testEncodeObjectWithAttributesOnly()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $endcoder->encodeData($author);

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
     * Test encode simple object as resource identity.
     */
    public function testEncodeObjectAsResourceIdentity()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ], $this->encoderOptions);

        $actual = $endcoder->encodeIdentifiers($author);

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
    public function testEncodeArrayAsResourceIdentity()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ], $this->encoderOptions);

        $actual = $endcoder->encodeIdentifiers([$author]);

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
    public function testEncodeObjectWithAttributesOnlyInArray()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $endcoder->encodeData([$author]);

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
    public function testEncodeObjectWithAttributesOnlyInAssocArray()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $endcoder->encodeData(['key_doesnt_matter' => $author]);

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
    public function testEncodeObjectWithAttributesOnlyPrettyPrinted()
    {
        $author   = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        $actual = $endcoder->encodeData($author);

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
    public function testEncodeArrayOfObjectsWithAttributesOnly()
    {
        $author1  = Author::instance(7, 'First', 'Last');
        $author2  = Author::instance(9, 'Dan', 'Gebhardt');
        $endcoder = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions);

        $actual = $endcoder->encodeData([$author1, $author2]);

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
    public function testEncodeMetaAndtopLinksForSimpleObject()
    {
        $author = Author::instance(9, 'Dan', 'Gebhardt');
        $links  = [Link::SELF => new Link('/people/9')];
        $meta   = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ];

        $actual = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ], $this->encoderOptions)->encode($author, $links, $meta);
        // replace with ->withLinks($links)->withMeta($meta)->encodeData($author) when depreciated methods removed

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
    public function testEncodeMeta()
    {
        $meta = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ];

        $actual = Encoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ])->meta($meta);
        // replace with encodeMeta when depreciated method is removed

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
    public function testEncodeJsonApiVersion()
    {
        $actual = Encoder::instance([])->withJsonApiVersion(['some' => 'meta'])->encodeData(null);

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
     * Test encoding with JSON API version.
     */
    public function testEncodeJsonApiVersionDeprecated()
    {
        $endcoder = Encoder::instance([], new EncoderOptions(0, null, true, ['some' => 'meta']));

        $actual = $endcoder->encodeData(null);

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
}
