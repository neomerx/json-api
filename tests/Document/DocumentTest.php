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
use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Schema\SchemaFactory;
use \Neomerx\JsonApi\Document\DocumentFactory;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Document\Presenters\ElementPresenter;
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
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            true,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            true,
            true,
            false,
            false,
            false,
            false
        ));
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
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            false,
            false,
            true,
            true,
            true,
            true
        ));
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            false,
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
            false,
            false,
            false,
            false,
            false,
            false
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            true,
            true,
            true,
            true,
            true,
            [Link::FIRST => new Link('/first')]
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
                        "links"   : {
                            "self"    : "peopleSelfUrl/selfSubUrl",
                            "related" : "peopleSelfUrl/relatedSubUrl",
                            "first"   : "/first"
                        },
                        "data" : { "type" : "comments", "id" : "321" },
                        "meta" : { "some" : "comment meta" }
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
            false,
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
            true,
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('/selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            true,
            false,
            false,
            null
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
                        "data" : { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} }
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
            false,
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
            true,
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('/selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            true,
            false,
            false,
            null
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
        $this->document->addToData($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'selfUrl',
            false,
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
            true,
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            false,
            true,
            false,
            null
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
            false,
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
            'commentsSelfUrl',
            false,
            false,
            false,
            false,
            false,
            false
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            true,
            true,
            true,
            true,
            true,
            false,
            null
        );
        $this->document->addReferenceToData($parent, $link, $resource);
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
                    "relationship-name" : "peopleSelfUrl/relatedSubUrl"
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
            false,
            false,
            false,
            false,
            false,
            false
        ));
        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            false,
            true,
            false,
            null
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
                    "relationship-name" : []
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
            false,
            false,
            false,
            false,
            false,
            false
        ));
        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            false,
            true,
            false,
            null
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
                    "relationship-name" : null
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
            false,
            false,
            true,
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
            true,
            true,
            false,
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            false,
            false,
            true,
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
            true,
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            true,
            true,
            true,
            true,
            false,
            null
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
                        "links"   : {
                            "self"    : "peopleSelfUrl/selfSubUrl",
                            "related" : "peopleSelfUrl/relatedSubUrl"
                        },
                        "data" : { "type" : "comments", "id" : "321", "meta" : {"some" : "comment meta"} },
                        "meta" : { "some" : "comment meta" }
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
            false,
            false,
            true,
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
            true,
            true,
            true,
            true,
            true,
            true
        );
        $link = $this->schemaFactory->createRelationshipObject(
            'comments-relationship',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            false,
            false,
            true,
            false,
            false,
            null
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            false,
            false,
            true,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createRelationshipObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            true,
            true,
            true,
            true,
            false,
            null
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
                    "link-name" : []
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
        $this->document->addToIncluded($parent = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'author meta'],
            'peopleSelfUrl/',
            false,
            false,
            true,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createRelationshipObject(
            'link-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            false,
            true,
            true,
            true,
            true,
            false,
            null
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
                    "link-name" : null
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
            false,
            false,
            true,
            true,
            true,
            true
        ));
        $link = $this->schemaFactory->createRelationshipObject(
            'relationship-name',
            new stdClass(), // in reality it will be a Comment class instance where $resource properties were taken from
            $this->createLink('selfSubUrl'),
            $this->createLink('relatedSubUrl'),
            true,
            true,
            true,
            true,
            true,
            false,
            null
        );
        $this->document->addReferenceToIncluded($parent, $link);
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
                    "relationship-name" : "peopleSelfUrl/relatedSubUrl"
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
        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            false,
            'people',
            '123',
            [],
            null,
            '',
            false,
            false,
            false,
            false,
            false,
            false
        ));
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
            'some-href',
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
                "href"   : "some-href",
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
     * Test URL concatenation.
     */
    public function testConcatUrls()
    {
        $presenter = new ElementPresenter();

        $this->assertEquals('url/subUrl', $presenter->concatUrls('url', $this->createLink('subUrl')));
        $this->assertEquals('url/subUrl', $presenter->concatUrls('url/', $this->createLink('subUrl')));
        $this->assertEquals('url/subUrl', $presenter->concatUrls('url', $this->createLink('/subUrl')));
        $this->assertEquals('url/subUrl', $presenter->concatUrls('url/', $this->createLink('/subUrl')));
    }

    /**
     * Test unset data.
     */
    public function testUnsetData()
    {
        $this->document->setMetaToDocument([
            "some" => "values",
        ]);

        $this->document->addToData($resource = $this->schemaFactory->createResourceObject(
            true,
            'people',
            '123',
            ['firstName' => 'John', 'lastName' => 'Dow'],
            ['some' => 'meta'],
            'selfUrl',
            true,
            true,
            false,
            false,
            false,
            false
        ));
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
}
