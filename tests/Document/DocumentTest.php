<?php namespace Neomerx\Tests\JsonApi\Document;

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

use \ArrayIterator;
use \EmptyIterator;
use \Neomerx\JsonApi\Document\Factory;
use \Neomerx\JsonApi\Document\Document;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Document\ElementInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class DocumentTest extends BaseTestCase
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory  = new Factory();
        $this->document = new Document();
    }

    /**
     * Test set document meta.
     */
    public function testSetDocumentMeta()
    {
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
            "data" : {
                "type"  : "someType",
                "id"    : "1",
                "links" : {
                    "self" : "/self-url"
                }
            }
        }
EOL;

        $this->document->setMetaToDocument([
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ]);

        $this->document->addToData($this->getSimpleElement());

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test set document links.
     */
    public function testSetDocumentLinks()
    {
        $expected = <<<EOL
        {
            "links" : {
                "self"  : "/self",
                "prev"  : "/prev",
                "next"  : "/next",
                "first" : "/first",
                "last"  : "/last"
            },
            "data" : {
                "type" : "someType",
                "id"   : "1",
                "links" : {
                    "self" : "/self-url"
                }
            }
        }
EOL;

        $this->document->setSelfUrlToDocumentLinks('/self');
        $this->document->setPrevUrlToDocumentLinks('/prev');
        $this->document->setNextUrlToDocumentLinks('/next');
        $this->document->setFirstUrlToDocumentLinks('/first');
        $this->document->setLastUrlToDocumentLinks('/last');

        $this->document->addToData($this->getSimpleElement());

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test add included element.
     */
    public function testAddToIncluded()
    {
        $expected = <<<EOL
        {
            "data" : {
                "type" : "someType",
                "id"   : "1",
                "links" : {
                    "self" : "/self-url"
                }
            },
            "included" : [{
                "type"      : "someType",
                "id"        : "2",
                "attribute" : "value",
                "links" : {
                    "self" : "/someType/2"
                }
            }]
        }
EOL;

        $this->document->addToIncluded($this->factory->createElement(
            'someType',
            2,
            ['attribute' => 'value'],
            '/someType/2',
            new EmptyIterator(),
            null
        ));

        $this->document->addToData($this->getSimpleElement());

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test add resource meta.
     */
    public function testAddResourceMeta()
    {
        $expected = <<<EOL
        {
            "data" : {
                "type" : "someType",
                "id"   : "1",
                "links" : {
                    "self" : "/someType/2"
                },
                "meta" : {
                    "attribute" : "value"
                }
            }
        }
EOL;

        $this->document->addToData($this->factory->createElement(
            'someType',
            1,
            [],
            '/someType/2',
            new EmptyIterator(),
            ['attribute' => 'value']
        ));

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test add link objects.
     */
    public function testAddLinkObjects()
    {
        $expected = <<<EOL
        {
            "data" : {
                "type" : "someType",
                "id"   : "1",
                "links" : {
                    "self"    : "/someType/1",
                    "myItems" : {
                        "self"    : "/someType/1/links/linkType",
                        "related" : "/someType/1/linkTypes",
                        "linkage" : [
                            {"type" : "linkType", "id"   : "1"},
                            {"type" : "linkType", "id"   : "2"}
                        ],
                        "meta" : {
                            "attribute" : "value"
                        }
                    }
                }
            }
        }
EOL;

        $linksIterator = new ArrayIterator([$this->factory->createLink(
            'myItems',
            false,
            'linkType',
            [1, "2"],
            '/someType/1/links/linkType',
            '/someType/1/linkTypes',
            ['attribute' => 'value']
        )]);
        $this->document->addToData(
            $this->factory->createElement('someType', 1, [], '/someType/1', $linksIterator, null)
        );

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test add link object.
     */
    public function testAddLinkObject()
    {
        $expected = <<<EOL
        {
            "data" : {
                "type" : "someType",
                "id"   : "1",
                "links" : {
                    "self"    : "/someType/1",
                    "myItems" : {
                        "self"    : "/someType/1/links/linkType",
                        "related" : "/someType/1/linkTypes",
                        "linkage" : {
                            "type" : "linkType",
                            "id"   : "1"
                        },
                        "meta"    : {
                            "attribute" : "value"
                        }
                    }
                }
            }
        }
EOL;

        $linksIterator = new ArrayIterator([$this->factory->createLink(
            'myItems',
            false,
            'linkType',
            [1],
            '/someType/1/links/linkType',
            '/someType/1/linkTypes',
            ['attribute' => 'value']
        )]);
        $this->document->addToData(
            $this->factory->createElement('someType', 1, [], '/someType/1', $linksIterator, null)
        );

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test show relation as URL.
     */
    public function testLinkAsUrl()
    {
        $expected = <<<EOL
        {
            "data" : {
                "type"     : "someType",
                "id"       : "1",
                "links" : {
                    "self"    : "/someType/1",
                    "myItems" : "/someType/1/linkTypes"
                }
            }
        }
EOL;

        $linksIterator = new ArrayIterator([$this->factory->createLink(
            'myItems',
            true,
            'linkTypes',
            [1, "2"],
            '/does/not/matter',
            '/someType/1/linkTypes',
            ['doesnt' => 'matter']
        )]);
        $this->document->addToData(
            $this->factory->createElement('someType', 1, [], '/someType/1', $linksIterator, null)
        );

        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, json_encode($this->document->getDocument()));
    }

    /**
     * Test add error info.
     */
    public function testAddErrorInfo()
    {
        $this->document->addToData($this->getSimpleElement());

        $this->document->addError($this->factory->createError(
            5,
            '/href',
            null,
            'some-error-code',
            null,
            null,
            null,
            null,
            ['extra' => 'member']
        ));

        $jsonAsArray = $this->document->getDocument();
        $this->assertNotEmpty($jsonAsArray);

        $this->assertFalse(isset($jsonAsArray[Document::KEYWORD_DATA]));
        $this->assertTrue(isset($jsonAsArray[Document::KEYWORD_ERRORS]));

        $errors = $jsonAsArray[Document::KEYWORD_ERRORS];
        $this->assertCount(1, $errors);
        $this->assertTrue(isset($errors[0][Document::KEYWORD_ERRORS_ID]));
        $this->assertTrue(isset($errors[0][Document::KEYWORD_ERRORS_HREF]));
        $this->assertFalse(isset($errors[0][Document::KEYWORD_ERRORS_STATUS]));
        $this->assertTrue(isset($errors[0][Document::KEYWORD_ERRORS_CODE]));
        $this->assertEquals('member', $errors[0]['extra']);
        $this->assertCount(4, $errors[0]);
    }

    /**
     * @return ElementInterface
     */
    private function getSimpleElement()
    {
        $element = $this->factory->createElement('someType', 1, [], '/self-url', new EmptyIterator, null);
        return $element;
    }
}
