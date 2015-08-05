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

use \Mockery;
use \stdClass;
use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
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

        $this->documentFactory = $this->schemaFactory = new Factory();

        $this->document = $this->documentFactory->createDocument();
    }

    /**
     * Test set document links.
     */
    public function testSetDocumentLinks()
    {
        $this->document->setDocumentLinks([
            Link::SELF  => new Link($selfUrl = 'selfUrl'),
            Link::FIRST => new Link($firstUrl = 'firstUrl'),
            Link::LAST  => new Link($lastUrl = 'lastUrl'),
            Link::PREV  => new Link($prevUrl = 'prevUrl'),
            Link::NEXT  => new Link($nextUrl = 'nextUrl'),
        ]);

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
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('selfUrl'),
            true,
            ['some' => 'meta']
        ), new stdClass(), true));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data" : [
                {
                    "type"       : "people",
                    "id"         : "123",
                    "attributes" : {
                        "firstName" : "John",
                        "lastName"  : "Dow"
                    },
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
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            null, // self url
            false, // show self
            null   // meta
        ), new stdClass(), false));

        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                }
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl'), // self url
            false, // show self
            null // meta
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('commentsSelfUrl/'),
            true, // show self
            ['this meta' => 'wont be included'],
            false,
            false,
            null,
            ['some' => 'comment meta']
        ), new stdClass(), false);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [
                LinkInterface::SELF    => $this->createLink('selfSubUrl'),
                LinkInterface::RELATED => $this->createLink('relatedSubUrl'),
                LinkInterface::FIRST   => new Link('/first', null, true),
            ],
            ['some' => 'relationship meta'],
            true,
            false // is root
        );
        $this->document->addRelationshipToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : { "type" : "comments", "id" : "321", "meta" : { "some" : "comment meta" } },
                        "meta" : { "some" : "relationship meta" },
                        "links"   : {
                            "self"    : "peopleSelfUrl/selfSubUrl",
                            "related" : "peopleSelfUrl/relatedSubUrl",
                            "first"   : "/first"
                        }
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add link to 'data' section. Hide link members except relationships.
     */
    public function testAddLinkToDataHideLinkMembersExceptRelationships()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('selfUrl/'), // self url
            false, // show self
            null //   meta
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('commentsSelfUrl/'),
            true, // show self
            ['this meta' => 'wont be shown'], // meta when resource is primary
            false,
            false,
            ['this meta' => 'wont be shown'], // meta when resource within 'included'
            ['some' => 'comment meta'] // meta when resource is in relationship
        ), new stdClass(), false);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );
        $this->document->addRelationshipToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"      : "people",
                "id"        : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : { "type" : "comments", "id" : "321", "meta" : { "some" : "comment meta" } }
                    }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add multiple items to relationship's 'data' section. Hide link members except linkage.
     */
    public function testAddMultipleRelationshipItemsToData()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('selfUrl/'), // self url
            false, // show self
            null //   meta
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('selfUrlWillBeHidden/'),
            true, // show self
            ['this meta' => 'wont be shown'], // meta when resource is primary
            false, // show 'self' in 'included'
            false, // show relationships in 'included'
            ['this meta' => 'wont be shown'], // meta when resource within 'included'
            ['some' => 'comment meta'] // meta when resource is in relationship
        ), new stdClass(), true);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );
        $this->document->addRelationshipToData($parent, $link, $resource);
        $this->document->addRelationshipToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : [
                            { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} },
                            { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} }
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('selfUrl/'), // self url
            false, // show self
            null //   meta
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('selfUrlWillBeHidden/'),
            true, // show self
            ['this meta' => 'wont be shown'], // meta when resource is primary
            false, // show 'self' in 'included'
            false, // show relationships in 'included'
            ['this meta' => 'wont be shown'], // meta when resource within 'included'
            ['some' => 'comment meta'] // meta when resource is in relationship
        ), new stdClass(), false);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //    links
            ['some' => 'relationship meta'], //  relationship meta
            false, // show data
            false //  is root
        );

        $this->document->addRelationshipToData($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "meta" : { "some" : "relationship meta" }
                    }
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null   // meta
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //    links
            ['some' => 'relationship meta'], //  relationship meta
            true, // show data
            false // is root
        );

        $this->document->addEmptyRelationshipToData($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "relationship-name" : {
                        "data" : [],
                        "meta" : { "some" : "relationship meta"}
                    }
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null //   meta
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );

        $this->document->addNullRelationshipToData($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "relationship-name" : {
                        "data" : null
                    }
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
        $this->document->addToIncluded($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null, //  meta
            true, //  show 'self' in 'included'
            false, // show 'relationships' in 'included'
            ['some' => 'meta'] // meta when resource within 'included'
        ), new stdClass(), false));

        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [
                {
                    "type"       : "people",
                    "id"         : "123",
                    "attributes" : {
                        "firstName" : "John",
                        "lastName"  : "Dow"
                    },
                    "links" : {
                        "self" : "peopleSelfUrl/"
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
        $this->document->addToIncluded($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null //   meta
        ), new stdClass(), false));

        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [
                {
                    "type"       : "people",
                    "id"         : "123",
                    "attributes" : {
                        "firstName" : "John",
                        "lastName"  : "Dow"
                    }
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl'), // self url
            false, // show self
            ['this meta' => 'wont be shown'], // meta when primary resource
            true, //  show 'self' in 'included'
            false, // show 'relationships' in 'included'
            ['some' => 'author meta'] // meta when resource within 'included'
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('selfUrlWillBeHidden/'),
            true, // show self
            ['this meta' => 'wont be shown'], // meta when resource is primary
            false, // show 'self' in 'included'
            false, // show relationships in 'included'
            ['this meta' => 'wont be shown'], // meta when resource within 'included'
            ['some' => 'comment meta'] // meta when resource is in relationship
        ), new stdClass(), false);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [
                LinkInterface::SELF    => new Link('selfSubUrl'),
                LinkInterface::RELATED => new Link('relatedSubUrl'),
            ],
            ['some' => 'relationship meta'], //  relationship meta
            true, // show data
            false // is root
        );

        $this->document->addRelationshipToIncluded($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} },
                        "meta" : { "some" : "relationship meta" },
                        "links"   : {
                            "self"    : "peopleSelfUrl/selfSubUrl",
                            "related" : "peopleSelfUrl/relatedSubUrl"
                        }
                    }
                },
                "links" : {
                    "self" : "peopleSelfUrl"
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            ['this meta' => 'wont be shown'], // meta when primary resource
            true, //  show 'self' in 'included'
            false, // show 'relationships' in 'included'
            ['some' => 'author meta'] // meta when resource within 'included'
        ), new stdClass(), false));

        $resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'comments',
            '321',
            null, // attributes
            new Link('selfUrlWillBeHidden/'),
            true, // show self
            ['this meta' => 'wont be shown'], // meta when resource is primary
            false, // show 'self' in 'included'
            false, // show relationships in 'included'
            ['this meta' => 'wont be shown'], // meta when resource within 'included'
            ['some' => 'comment meta'] // meta when resource is in relationship
        ), new stdClass(), false);

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], // links
            null, //  relationship meta
            true, //  show data
            false // is root
        );

        $this->document->addRelationshipToIncluded($parent, $link, $resource);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} }
                    }
                },
                "links" : {
                    "self" : "peopleSelfUrl/"
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            ['this meta' => 'wont be shown'], // meta when primary resource
            true, //  show 'self' in 'included'
            false, // show 'relationships' in 'included'
            ['some' => 'author meta'] // meta when resource within 'included'
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );

        $this->document->addEmptyRelationshipToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : []
                    }
                },
                "links" : {
                    "self" : "peopleSelfUrl/"
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            ['this meta' => 'wont be shown'], // meta when primary resource
            true, //  show 'self' in 'included'
            false, // show 'relationships' in 'included'
            ['some' => 'author meta'] // meta when resource within 'included'
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], // links
            null, // relationship meta
            true, // show data
            false // is root
        );

        $this->document->addNullRelationshipToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "comments-relationship" : {
                        "data" : null
                    }
                },
                "links" : {
                    "self" : "peopleSelfUrl/"
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
     * Test add type and id only. This functionality is required for replies on 'request links'.
     */
    public function testAddTypeAndIdOnly()
    {
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            null, // attributes
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null // meta when primary resource
        ), new stdClass(), false));
        $this->document->setResourceCompleted($resource);

        $expected = <<<EOL
        {
            "data" : {
                "type" : "people",
                "id"   : "123"
            }
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
        $this->document->setDocumentLinks([Link::SELF => new Link('selfUrl')]);

        $this->document->addError($this->documentFactory->createError(
            'some-id',
            new Link('about-link'),
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['source' => 'data'],
            ['meta' => 'data']
        ));

        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "links"  : {"about" : "about-link"},
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"meta" : "data"}
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add error.
     */
    public function testAddErrorWithIntegerStatusAndCode()
    {
        // First add something to document. When error is added nothing except errors must be in result
        $this->document->setDocumentLinks([Link::SELF => new Link('selfUrl')]);

        $this->document->addError($this->documentFactory->createError(
            'some-id',
            new Link('about-link'),
            500,
            1337,
            'some-title',
            'some-detail',
            ['source' => 'data'],
            ['meta' => 'data']
        ));

        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "links"  : {"about" : "about-link"},
                "status" : "500",
                "code"   : "1337",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"meta" : "data"}
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add JSON API version info.
     */
    public function testAddVersion()
    {
        $this->document->addJsonApiVersion('1.0', ['some' => 'meta']);
        $this->document->unsetData();

        $expected = <<<EOL
        {
            "jsonapi":{
                "version" : "1.0",
                "meta"    : { "some" : "meta" }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test unset data.
     */
    public function testUnsetData()
    {
        $this->document->setMetaToDocument([
            "some" => "values",
        ]);

        $this->document->addToData($resource = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            null, // attributes
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null // meta when primary resource
        ), new stdClass(), false));
        $this->document->setResourceCompleted($resource);

        $this->document->unsetData();

        $expected = <<<EOL
        {
            "meta" : { "some" : "values" }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add meta information to relationships.
     */
    public function testRelationshipsPrimaryMeta()
    {
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null, //   meta
            false, // show self in included
            false, // show relationships in included
            null, //  inclusion meta
            null, //  relationship meta
            [], //    include paths
            true, //  show attributes in included
            ['some' => 'relationships meta'] // relationships primary meta
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );

        $this->document->addNullRelationshipToData($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "relationship-name" : {
                        "data" : null
                    },
                    "meta" : { "some" : "relationships meta" }
                }
            }
        }
EOL;
        $this->check($expected);
    }

    /**
     * Test add meta information to relationships.
     */
    public function testRelationshipsInclusionMeta()
    {
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject($this->getSchema(
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            new Link('peopleSelfUrl/'), // self url
            false, // show self
            null, //   meta
            false, // show self in included
            false, // show relationships in included
            null, //  inclusion meta
            null, //  relationship meta
            [], //    include paths
            true, //  show attributes in included
            null, //  relationships primary meta
            ['some' => 'relationships meta'] // relationships inclusion meta
        ), new stdClass(), false));

        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            [], //   links
            null, // relationship meta
            true, // show data
            false // is root
        );

        $this->document->addNullRelationshipToIncluded($parent, $link);
        $this->document->setResourceCompleted($parent);

        $expected = <<<EOL
        {
            "data"     : null,
            "included" : [{
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "firstName" : "John",
                    "lastName"  : "Dow"
                },
                "relationships" : {
                    "relationship-name" : {
                        "data" : null
                    },
                    "meta" : { "some" : "relationships meta" }
                }
            }]
        }
EOL;
        $this->check($expected);
    }

    /**
     * @param string $subHref
     *
     * @return LinkInterface
     */
    private function createLink($subHref)
    {
        return $this->schemaFactory->createLink($subHref);
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

    /**
     * @param string        $type
     * @param string        $idx
     * @param array|null    $attributes
     * @param LinkInterface|null $selfLink
     * @param bool          $showSelfUrl
     * @param mixed         $primaryMeta
     * @param bool          $showSelfInIncluded
     * @param bool          $relShipsInIncluded
     * @param mixed         $inclusionMeta
     * @param mixed         $relationshipMeta
     * @param array         $includePaths
     * @param bool          $showAttributesInIncluded
     * @param mixed         $relPrimaryMeta
     * @param mixed         $relIncMeta
     *
     * @return SchemaProviderInterface
     */
    private function getSchema(
        $type,
        $idx,
        $attributes,
        $selfLink,
        $showSelfUrl,
        $primaryMeta,
        $showSelfInIncluded = false,
        $relShipsInIncluded = false,
        $inclusionMeta = null,
        $relationshipMeta = null,
        $includePaths = [],
        $showAttributesInIncluded = true,
        $relPrimaryMeta = null,
        $relIncMeta = null
    ) {
        $schema = Mockery::mock(SchemaProviderinterface::class);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getResourceType')->zeroOrMoreTimes()->andReturn($type);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getId')->zeroOrMoreTimes()->andReturn($idx);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getSelfSubLink')->zeroOrMoreTimes()->andReturn($selfLink);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getAttributes')->zeroOrMoreTimes()->andReturn($attributes);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('isShowSelf')->zeroOrMoreTimes()->andReturn($showSelfUrl);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('isShowSelfInIncluded')->zeroOrMoreTimes()->andReturn($showSelfInIncluded);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('isShowAttributesInIncluded')->zeroOrMoreTimes()->andReturn($showAttributesInIncluded);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('isShowRelationshipsInIncluded')->zeroOrMoreTimes()->andReturn($relShipsInIncluded);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getIncludePaths')->zeroOrMoreTimes()->andReturn($includePaths);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getPrimaryMeta')->zeroOrMoreTimes()->andReturn($primaryMeta);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getLinkageMeta')->zeroOrMoreTimes()->andReturn($relationshipMeta);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getInclusionMeta')->zeroOrMoreTimes()->andReturn($inclusionMeta);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getRelationshipsPrimaryMeta')->zeroOrMoreTimes()->andReturn($relPrimaryMeta);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema->shouldReceive('getRelationshipsInclusionMeta')->zeroOrMoreTimes()->andReturn($relIncMeta);

        return $schema;
    }
}
