<?php namespace Neomerx\Tests\JsonApi\Sample;

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

use Neomerx\Samples\JsonApi\Application\EncodeSamples;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeTest extends BaseTestCase
{
    /**
     * @var EncodeSamples
     */
    private $samples;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->samples = new EncodeSamples();
    }

    /**
     * Test encode sample.
     */
    public function testBasicExample()
    {
        $actual   = $this->samples->getBasicExample();
        $expected = <<<EOL
        {
            "data" : {
                "type" : "people",
                "id"   : "123",
                "attributes" : {
                    "first_name" : "John",
                    "last_name"  : "Dow"
                },
                "links" : {
                    "self" : "http://example.com/api/v1/people/123"
                }
            }
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual));
    }

    /**
     * Test encode sample.
     */
    public function testIncludedObjectsExample()
    {
        $actual   = $this->samples->getIncludedObjectsExample();
        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "1",
                "attributes" : {
                    "name" : "JSON API Samples"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [
                            { "type" : "posts", "id" : "321" }
                        ],
                        "links" : {
                            "some-sublink"  : "http://example.com/sites/1/resource-sublink",
                            "external-link" : "www.example.com"
                        }
                    }
                },
                "links" : {
                    "self" : "http://example.com/sites/1"
                }
            },
            "included" : [
                {
                    "type" : "people",
                    "id"   : "123",
                    "attributes" : {
                        "first_name" : "John",
                        "last_name" : "Dow"
                    }
                }, {
                    "type" : "comments",
                    "id"   : "456",
                    "attributes" : {
                        "body" : "Included objects work as easy as basic ones"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "123" }
                        }
                    }
                }, {
                    "type" : "comments",
                    "id"   : "789",
                    "attributes" : {
                        "body" : "Let's try!"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "123" }
                        }
                    }
                }, {
                    "type" : "posts",
                    "id"   : "321",
                    "attributes" : {
                        "title" : "Included objects",
                        "body"  : "Yes, it is supported"
                    },
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "123" }
                        },
                        "comments" : {
                            "data" : [
                                { "type" : "comments", "id" : "456" },
                                { "type" : "comments", "id" : "789" }
                            ]
                        }
                    }
                }
            ]
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual));
    }

    /**
     * Test encode sample.
     */
    public function testSparseAndFieldSetsExample()
    {
        $actual   = $this->samples->getSparseAndFieldSetsExample();
        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "1",
                "attributes" : {
                    "name" : "JSON API Samples"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [
                            { "type" : "posts", "id" : "321" }
                        ]
                    }
                },
                "links" : {
                    "self" : "/sites/1"
                }
            },
            "included":[
                {
                    "type" : "people",
                    "id"   : "123",
                    "attributes" : {
                        "first_name" : "John"
                    }
                }, {
                    "type" : "posts",
                    "id"   : "321",
                    "relationships" : {
                        "author" : {
                            "data" : { "type" : "people", "id" : "123" }
                        }
                    }
                }
            ]
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual));
    }

    /**
     * Test encode sample.
     */
    public function testTopLevelMetaAndLinksExample()
    {
        $actual   = $this->samples->getTopLevelMetaAndLinksExample();
        $expected = <<<EOL
        {
            "meta" : {
                "copyright" : "Copyright 2015 Example Corp.",
                "authors"   : [
                    "Yehuda Katz",
                    "Steve Klabnik",
                    "Dan Gebhardt"
                ]
            },
            "links" : {
                "first" : "http://example.com/people?first",
                "last"  : "http://example.com/people?last",
                "prev"  : "http://example.com/people?prev",
                "next"  : "http://example.com/people?next"
            },
            "data" : {
                "type" : "people",
                "id"   : "123",
                "attributes" : {
                    "first_name" : "John",
                    "last_name"  : "Dow"
                },
                "links" : {
                    "self" : "http://example.com/people/123"
                }
            }
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual));
    }

    /**
     * Test encode sample.
     */
    public function testDynamicSchemaExample()
    {
        $actual   = $this->samples->getDynamicSchemaExample();
        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "1",
                "attributes" : {
                    "name" : "JSON API Samples"
                },
                "relationships" : {
                    "posts" : {
                        "data" : []
                    }
                },
                "links" : {
                    "self" : "/sites/1"
                }
            }
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual[0]));

        $expected = <<<EOL
        {
            "data" : {
                "type" : "sites",
                "id"   : "1",
                "attributes" : {
                    "name" : "JSON API Samples"
                },
                "relationships" : {
                    "posts" : {
                        "data" : [],
                        "links" : {
                            "some-sublink"  : "/sites/1/resource-sublink",
                            "external-link" : "www.example.com"
                        }
                    }
                },
                "links" : {
                    "self" : "/sites/1"
                }
            }
        }
EOL;

        $this->assertEquals($this->normalize($expected), $this->normalize($actual[1]));
    }

    /**
     * Test performance sample.
     */
    public function testPerformanceTestForSmallNestedResources()
    {
        $this->assertGreaterThan(0, $this->samples->runPerformanceTestForSmallNestedResources(10)[0]);
    }

    /**
     * Test performance sample.
     */
    public function testPerformanceTestForBigCollection()
    {
        $this->assertGreaterThan(0, $this->samples->runPerformanceTestForBigCollection(10)[0]);
    }

    /**
     * @param string $json
     *
     * @return string
     */
    private function normalize($json)
    {
        return json_encode(json_decode($json));
    }
}
