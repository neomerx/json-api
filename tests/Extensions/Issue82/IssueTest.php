<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue82;

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

use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test very basic sample to be used in project description.
     */
    public function testEnheritedEncoder(): void
    {
        $author  = Author::instance(123, 'John', 'Doe');

        $encoder = Encoder::instance([
                Author::class => AuthorSchema::class,
            ])
            ->withUrlPrefix('http://example.com/api/v1')
            ->withEncodeOptions(JSON_PRETTY_PRINT);

        $actual = $encoder->encodeData($author);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "people",
                "id"         : "123",
                "attributes" : {
                    "first-name": "John",
                    "last-name": "Doe"
                },
                "relationships" : {
                    "comments" : {
                        "links": {
                            "related" : "http://example.com/api/v1/people/123/comments"
                        }
                    }
                },
                "links" : {
                    "self" : "http://example.com/api/v1/people/123"
                }
            }
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }
}
