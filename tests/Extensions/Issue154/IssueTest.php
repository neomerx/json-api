<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue154;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Schemas\AuthorSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test late Schema assignment.
     *
     * @see https://github.com/neomerx/json-api/issues/154
     */
    public function testEnheritedEncoder()
    {
        $encoder = CustomEncoder::instance();
        /** @var CustomEncoder $encoder */
        $encoder->addSchema(Author::class, AuthorSchema::class);

        $author = Author::instance(9, 'Dan', 'Gebhardt');
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
                "relationships" : {
                    "comments" : {
                        "links": {
                            "self"    : "/people/9/relationships/comments",
                            "related" : "/people/9/comments"
                        }
                  }
                },
                "links" : {
                    "self" : "/people/9"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }
}
