<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue169;

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

use Neomerx\JsonApi\Document\Error;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Author;
use Neomerx\Tests\JsonApi\Data\AuthorSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test encoder will serialize into arrays.
     *
     * @see https://github.com/neomerx/json-api/issues/169
     */
    public function testDataSerialization()
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = CustomEncoder::instance([
            Author::class => function ($factory) {
                $schema = new AuthorSchema($factory);
                $schema->linkRemove(Author::LINK_COMMENTS);
                return $schema;
            }
        ]);

        $actual = $encoder->serializeData($author);

        $expected = [
            'data' => [
                'type'       => 'people',
                'id'         => '9',
                'attributes' => [
                    'first_name' => 'Dan',
                    'last_name'  => 'Gebhardt'
                ],
                'links'      => [
                    'self' => '/people/9'
                ]
            ]
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encoder will serialize into arrays.
     *
     * @see https://github.com/neomerx/json-api/issues/169
     */
    public function testIdentifiersSerialization()
    {
        $author  = Author::instance(9, 'Dan', 'Gebhardt');
        $encoder = CustomEncoder::instance([
            Author::class => AuthorSchema::class
        ]);

        $actual = $encoder->serializeIdentifiers($author);

        $expected = [
            'data' => [
                'type' => 'people',
                'id'   => '9',
            ]
        ];

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test encoder will serialize into arrays.
     *
     * @see https://github.com/neomerx/json-api/issues/169
     */
    public function testErrorSerialization()
    {
        $error   = new Error('some-id');
        $encoder = CustomEncoder::instance();

        $this->assertEquals(['errors' => [['id' => 'some-id']]], $encoder->serializeError($error));
        $this->assertEquals(['errors' => [['id' => 'some-id']]], $encoder->serializeErrors([$error]));
    }

    /**
     * Test encoder will serialize into arrays.
     *
     * @see https://github.com/neomerx/json-api/issues/169
     */
    public function testMetaSerialization()
    {
        $meta    = ['some meta'];
        $encoder = CustomEncoder::instance();

        $this->assertEquals(['meta' => $meta], $encoder->serializeMeta($meta));
    }
}
