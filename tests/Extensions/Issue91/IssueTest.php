<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue91;

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
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test encode Category hierarchy.
     */
    public function testEncodeHierarchy()
    {
        $hierarchy = $this->createHierarchy();

        $actual = Encoder::instance([
            Category::class => CategorySchema::class,
        ])->encodeData($hierarchy);

        $expected = <<<EOL
{
    "data" : [
        {
            "type" : "categories",
            "id"   : "1",
            "attributes" : {
                "description" : "Main"
            },
            "relationships" : {
                "parent" : {
                    "data" : null
                }
            },
            "links" : {
                "self" : "/categories/1"
            }
        },{
            "type" : "categories",
            "id"   : "2",
            "attributes" : {
                "description" : "Laptop"
            },
            "relationships" : {
                "parent" : {
                    "data" : { "type" : "categories", "id" : "1" }
                }
            },
            "links" : {
                "self" : "/categories/2"
            }
        },{
            "type" : "categories",
            "id"   : "3",
            "attributes" : {
                "description" : "PC"
            },
            "relationships" : {
                "parent" : {
                    "data" : { "type" : "categories", "id" : "1" }
                }
            },
            "links" : {
                "self" : "/categories/3"
            }
        },{
            "type" : "categories",
            "id"   : "4",
            "attributes" : {
                "description" : "Screen"
            },
            "relationships" : {
                "parent" : {
                    "data" : { "type" : "categories", "id" : "2" }
                }
            },
            "links" : {
                "self" : "/categories/4"
            }
        },{
            "type" : "categories",
            "id"   : "5",
            "attributes" : {
                "description" : "Big"
            },
            "relationships" : {
                "parent" : {
                    "data" : { "type" : "categories", "id" : "4" }
                }
            },
            "links" : {
                "self" : "/categories/5"
            }
        },{
            "type" : "categories",
            "id"   : "6",
            "attributes" : {
                "description" : "Small"
            },
            "relationships" : {
                "parent" : {
                    "data" : { "type" : "categories", "id" : "4" }
                }
            },
            "links" : {
                "self" : "/categories/6"
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
     * @return Category[]
     */
    private function createHierarchy()
    {
        $main   = new Category(1, 'Main');
        $laptop = new Category(2, 'Laptop', $main);
        $pc     = new Category(3, 'PC', $main);
        $screen = new Category(4, 'Screen', $laptop);
        $big    = new Category(5, 'Big', $screen);
        $small  = new Category(6, 'Small', $screen);

        return [$main, $laptop, $pc, $screen, $big, $small];
    }
}
