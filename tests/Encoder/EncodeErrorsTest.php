<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Encoder;

/**
 * Copyright 2015-2020 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Schema\LinkWithAliases;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeErrorsTest extends BaseTestCase
{
    /**
     * Test encode error.
     */
    public function testEncodeError(): void
    {
        $error   = $this->getError();
        $encoder = Encoder::instance();

        $actual   = $encoder->encodeError($error);
        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "links"  : {
                    "about" : "about-link",
                     "type" : [{ "href": "http://example.com/errors/123", "aliases": {"v": "version"} }]
                },
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode error array.
     */
    public function testEncodeErrorsArray(): void
    {
        $error   = $this->getError();
        $encoder = Encoder::instance();

        $actual = $encoder->encodeErrors([$error]);

        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "links"  : {
                    "about" : "about-link",
                     "type" : [{ "href": "http://example.com/errors/123", "aliases": {"v": "version"} }]
                },
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode error array.
     */
    public function testEncodeErrorsCollection(): void
    {
        $errors = new ErrorCollection();
        $errors->add($this->getError());

        $encoder = Encoder::instance();

        $actual = $encoder->encodeErrors($errors);

        $expected = <<<EOL
        {
            "errors":[{
                "id"     : "some-id",
                "links"  : {
                    "about" : "about-link",
                     "type" : [{ "href": "http://example.com/errors/123", "aliases": {"v": "version"} }]
                },
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode empty error.
     *
     * @see https://github.com/neomerx/json-api/issues/62
     */
    public function testEncodeEmptyError(): void
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
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode empty error array.
     *
     * @see https://github.com/neomerx/json-api/issues/151
     */
    public function testEncodeEmptyErrorArray(): void
    {
        $actual = Encoder::instance()->encodeErrors([]);

        $expected = <<<EOL
        {
            "errors" : []
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * Test encode error.
     *
     * @see https://github.com/neomerx/json-api/issues/171
     */
    public function testEncodeErrorWithMetaAndJsonApi(): void
    {
        $error   = $this->getError();
        $encoder = Encoder::instance();

        $actual = $encoder
            ->withJsonApiVersion(Encoder::JSON_API_VERSION)
            ->withJsonApiMeta(['some' => 'meta'])
            ->withMeta(["copyright" => "Copyright 2015 Example Corp."])
            ->encodeError($error);

        $expected = <<<EOL
        {
            "jsonapi" : {
                "version" : "1.1",
                "meta"    : { "some" : "meta" }
            },
            "meta" : {
                "copyright" : "Copyright 2015 Example Corp."
            },
            "errors":[{
                "id"     : "some-id",
                "links"  : {
                    "about" : "about-link",
                     "type" : [{ "href": "http://example.com/errors/123", "aliases": {"v": "version"} }]
                },
                "status" : "some-status",
                "code"   : "some-code",
                "title"  : "some-title",
                "detail" : "some-detail",
                "source" : {"source" : "data"},
                "meta"   : {"some" : "meta"}
            }]
        }
EOL;
        self::assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     * @return ErrorInterface
     */
    private function getError(): ErrorInterface
    {
        return new Error(
            'some-id',
            new Link(false, 'about-link', false),
            [new LinkWithAliases(false, 'http://example.com/errors/123', ['v' => 'version'], false)],
            'some-status',
            'some-code',
            'some-title',
            'some-detail',
            ['source' => 'data'],
            true,
            ['some' => 'meta']
        );
    }
}
