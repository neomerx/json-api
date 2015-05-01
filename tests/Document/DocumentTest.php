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

use \stdClass;
use \ReflectionMethod;
use \Neomerx\JsonApi\Document\Document;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Schema\SchemaFactory;
use \Neomerx\JsonApi\Document\DocumentFactory;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class DocumentTest extends BaseTestCase
{
    /**
     * @var SchemaFactoryInterface
     */
    private $schemaFactory;

    /**
     * @var DocumentInterface
     */
    private $document;

    /**
     * @var DocumentFactoryInterface
     */
    private $documentFactory;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->schemaFactory   = new SchemaFactory();
        $this->documentFactory = new DocumentFactory();
        $this->document        = $this->documentFactory->createDocument();
    }

    /**
     * Test set document links.
     */
    public function testSetDocumentLinks()
    {
        $this->document->setDocumentLinks($this->documentFactory->createDocumentLinks(
            $selfUrl  = 'selfUrl',
            $firstUrl = 'firstUrl',
            $lastUrl  = 'lastUrl',
            $prevUrl  = 'prevUrl',
            $nextUrl  = 'nextUrl'
        ));

        $expected = <<<EOL
        {
            "links" : {
                "self"  : "selfUrl",
                "first" : "firstUrl",
                "last"  : "lastUrl",
                "prev"  : "prevUrl",
                "next"  : "nextUrl"
            },
            "data" : null
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test set document meta.
     */
    public function testSetMetaToDocument()
    {
        $this->document->setMetaToDocument([
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ]);

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
            "data" : null
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add to 'data' section. Resource in array. Members are shown.
     */
    public function testAddToDataArrayedShowMembers()
    {
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            true,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            'some-self-controller-data',
            true,
            true,
            false,
            false,
            false
        ));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"      : "people",
                    "id"        : "123",
                    "firstName" : "John",
                    "lastName"  : "Dow",
                    "links" : {
                        "self" : "selfUrl"
                    },
                    "meta" : {
                        "some" : "meta"
                    }
                }
            ]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add to 'data' section. Resource not in array. Members are hidden.
     */
    public function testAddToDataNotArrayedHiddenMembers()
    {
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow"
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test set [] to 'data' section.
     */
    public function testSetEmptyData()
    {
        $this->document->setEmptyData();

        $expected = <<<EOL
        {
            "data" : []
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test set null to 'data' section.
     */
    public function testSetNullData()
    {
        $this->document->setNullData();

        $expected = <<<EOL
        {
            "data" : null
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'data' section. Show link members.
     */
    public function testAddLinkToDataShowLinkMembers()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'commentsSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            true,
            true,
            true,
            true,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : {
                        "self"    : "peopleSelfUrl/selfSubUrl",
                        "related" : "peopleSelfUrl/relatedSubUrl",
                        "meta"    : { "some" : "comment meta" },
                        "linkage" : { "type" : "comments", "id" : "321" }
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'data' section. Hide link members except linkage.
     */
    public function testAddLinkToDataHideLinkMembersExceptLinkage()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl/',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'selfUrl/',
            'some-self-controller-data',
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            '/selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            true,
            false,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : {
                        "linkage" : { "type" : "comments", "id" : "321" }
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add multiple links to 'data' section. Hide link members except linkage.
     */
    public function testAddMultipleLinksToData()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl/',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'selfUrl/',
            'some-self-controller-data',
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            '/selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            true,
            false,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToData($parent, $link, $resource);
        $this->document->addLinkToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : {
                        "linkage" : [
                            { "type" : "comments", "id" : "321" },
                            { "type" : "comments", "id" : "321" }
                        ]
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'data' section. Hide link members except meta.
     */
    public function testAddLinkToDataHideLinkMembersExceptMeta()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'selfUrl/',
            'some-self-controller-data',
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            false,
            true,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : {
                        "meta" : { "some" : "comment meta" }
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link as reference to 'data' section.
     */
    public function testAddReferenceToData()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'ommentsSelfUrl',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            true,
            true,
            true,
            true,
            true,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addReferenceToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : "peopleSelfUrl/relatedSubUrl"
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add empty (empty array) link to 'data' section.
     */
    public function testAddEmptyLinkToData()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            false,
            true,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addEmptyLinkToData($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : []
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add null link to 'data' section.
     */
    public function testAddNullLinkToData()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl',
            'some-self-controller-data',
            false,
            false,
            false,
            false,
            false
        ));
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            false,
            true,
            false,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addNullLinkToData($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "link-name" : null
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add to 'included' section. Members are shown.
     */
    public function testAddToIncludedShowMembers()
    {
        $this->document->addToIncluded($resource = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [
                {
                    "type"      : "people",
                    "id"        : "123",
                    "firstName" : "John",
                    "lastName"  : "Dow",
                    "links" : {
                        "self" : "selfUrl"
                    },
                    "meta" : {
                        "some" : "meta"
                    }
                }
            ]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add to 'included' section. Members are hidden.
     */
    public function testAddToIncludedHideMembers()
    {
        $this->document->addToIncluded($resource = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            'some-self-controller-data',
            true,
            true,
            false,
            false,
            false
        ));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [
                {
                    "type"      : "people",
                    "id"        : "123",
                    "firstName" : "John",
                    "lastName"  : "Dow"
                }
            ]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'included' section. Show link members.
     */
    public function testAddLinkToIncludedShowLinkMembers()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'commentsSelfUrl/',
            'some-self-controller-data',
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            true,
            true,
            true,
            true,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToIncluded($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "self" : "peopleSelfUrl/",
                    "link-name" : {
                        "self"    : "peopleSelfUrl/selfSubUrl",
                        "related" : "peopleSelfUrl/relatedSubUrl",
                        "meta"    : { "some" : "comment meta" },
                        "linkage" : { "type" : "comments", "id" : "321" }
                    }
                },
                "meta" : {
                    "some" : "author meta"
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'included' section. Hide members for linked resource.
     */
    public function testAddLinkToIncludedHideMembersForLinkedResource()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $resource = $this->schemaFactory->createResourceObject(
            false,
            'comments',
            '321',
            ['title' => 'some title', 'body' => 'some body'],
            ['some' => 'comment meta'],
            'commentsSelfUrl/',
            'some-self-controller-data',
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            false,
            false,
            true,
            false,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addLinkToIncluded($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "self" : "peopleSelfUrl/",
                    "link-name" : {
                        "linkage" : { "type" : "comments", "id" : "321" }
                    }
                },
                "meta" : {
                    "some" : "author meta"
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add empty (empty array) link to 'included' section.
     */
    public function testAddEmptyLinkToIncluded()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            true,
            true,
            true,
            true,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addEmptyLinkToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "self"      : "peopleSelfUrl/",
                    "link-name" : []
                },
                "meta" : {
                    "some" : "author meta"
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add null link to 'included' section.
     */
    public function testAddNullLinkToIncluded()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            false,
            true,
            true,
            true,
            true,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addNullLinkToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "self"      : "peopleSelfUrl/",
                    "link-name" : null
                },
                "meta" : {
                    "some" : "author meta"
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add reference link to 'included' section.
     */
    public function testAddReferenceLinkToIncluded()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            'some-self-controller-data',
            false,
            false,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createLinkObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            'selfSubUrl',
            'relatedSubUrl',
            true,
            true,
            true,
            true,
            true,
            true,
            'some-self-controller-data',
            'some-related-controller-data'
        );
        $this->document->addReferenceToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"      : "people",
                "id"        : "123",
                "firstName" : "John",
                "lastName"  : "Dow",
                "links" : {
                    "self"      : "peopleSelfUrl/",
                    "link-name" : "peopleSelfUrl/relatedSubUrl"
                },
                "meta" : {
                    "some" : "author meta"
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add error.
     */
    public function testAddError()
    {
        // First add something to document. When error is added nothing except errors must be in result
        $this->document->setDocumentLinks($this->documentFactory->createDocumentLinks(
            $selfUrl  = 'selfUrl'
        ));

        $this->document->addError($this->documentFactory->createError(
            'some-id',
            'some-href',
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['link1'],
            ['paths'],
            ['additional' => 'members']
        ));

        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "href"   : "some-href",
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "links" : [
                    "link1"
                ],
                "paths" : [
                    "paths"
                ],
                "additional" : "members"
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test URL concatenation.
     */
    public function testConcatUrls()
    {
        // We can't test all possible routes within this function during normal testing.
        // That's why we use reflection to make it callable and invoke with all possible parameters.
        $reflectionMethod = new ReflectionMethod(Document::class, 'concatUrls');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('url/subUrl', $reflectionMethod->invoke($this->document, 'url', 'subUrl'));
        $this->assertEquals('url/subUrl', $reflectionMethod->invoke($this->document, 'url/', 'subUrl'));
        $this->assertEquals('url/subUrl', $reflectionMethod->invoke($this->document, 'url', '/subUrl'));
        $this->assertEquals('url/subUrl', $reflectionMethod->invoke($this->document, 'url/', '/subUrl'));
    }

    /**
     * @param string $expected
     */
    private function check($expected)
    {
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));
        $actual   = json_encode($this->document->getDocument());
        $this->assertEquals($expected, $actual);
    }
}
