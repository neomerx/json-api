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

use \Neomerx\JsonApi\Document\Link;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\ErrorCollection;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeErrorsTest extends BaseTestCase
{
    /**
     * Test encode error.
     */
    public function testEncodeError()
    {
        $error   = $this->getError();
        $encoder = Encoder::instance();

        $actual = $encoder->encodeError($error);

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
     * Test encode error array.
     */
    public function testEncodeErrorsArray()
    {
        $error   = $this->getError();
        $encoder = Encoder::instance();

        $actual = $encoder->encodeErrors([$error]);

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
     * Test encode error array.
     */
    public function testEncodeErrorsCollection()
    {
        $errors  = new ErrorCollection();
        $errors->add($this->getError());

        $encoder = Encoder::instance();

        $actual = $encoder->encodeErrors($errors);

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
     * Test encode empty error.
     *
     * @see https://github.com/neomerx/json-api/issues/62
     */
    public function testEncodeEmptyError()
    {
        $error   = new Error();
        $encoder = Encoder::instance();
        $actual  = $encoder->encodeError($error);

        $expected = <<<EOL
        {
            "errors":[
                {}
            ]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return Error
     */
    private function getError()
    {
        return new Error(
            'some-id',
            new Link('about-link'),
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['source' => 'data'],
            ['some' => 'meta']
        );
    }
}
