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
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeErrorsTest extends BaseTestCase
{
    /**
     * Test encode null.
     */
    public function testEncodeError()
    {
        $error = new Error(
            'some-id',
            new Link('about-link'),
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['source' => 'data'],
            ['some'   => 'meta']
        );

        $endcoder = Encoder::instance([]);

        $actual = $endcoder->error($error);

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
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encode null.
     */
    public function testEncodeErrors()
    {
        $error = new Error(
            'some-id',
            new Link('about-link'),
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['source' => 'data'],
            ['some'   => 'meta']
        );

        $endcoder = Encoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $endcoder->errors([$error]);

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
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
