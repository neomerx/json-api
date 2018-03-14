<?php namespace Neomerx\Tests\JsonApi\Http\Query;

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

use Generator;
use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Neomerx\JsonApi\Http\Query\BaseQueryParser;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Author;
use Neomerx\Tests\JsonApi\Data\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Comment;
use Neomerx\Tests\JsonApi\Data\CommentSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class BaseQueryParserTest extends BaseTestCase
{
    /**
     * Test query.
     */
    public function testEmptyQueryParams(): void
    {
        $queryParameters = [];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([], $this->iterableToArray($parser->getIncludes()));
        $this->assertEquals([], $this->iterableToArray($parser->getFields()));
    }

    /**
     * Test query.
     */
    public function testIncludes(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => 'comments,   comments.author',
        ];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([
            'comments'        => ['comments'],
            'comments.author' => ['comments', 'author'],
        ], $this->iterableToArray($parser->getIncludes()));
    }

    /**
     * That's a special case to test possible issues with `empty` function which thinks "0" is an empty string.
     */
    public function testIncludesForStringWithZeroes1(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => '0',
        ];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([
            '0' => ['0'],
        ], $this->iterableToArray($parser->getIncludes()));
    }

    /**
     * That's a special case to test possible issues with `empty` function which thinks "0" is an empty string.
     */
    public function testIncludesForStringWithZeroes2(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => '0,1',
        ];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([
            '0' => ['0'],
            '1' => ['1'],
        ], $this->iterableToArray($parser->getIncludes()));
    }

    /**
     * Test query.
     */
    public function testFields(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_FIELDS => [
                'articles' => 'title,     body      ',
                'people'   => 'name',
            ],
        ];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([
            'articles' => ['title', 'body'],
            'people'   => ['name'],
        ], $this->iterableToArray($parser->getFields()));
    }

    /**
     * Test query.
     */
    public function testSorts(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_SORT => '-created,title,+updated',
        ];

        $parser = $this->createParser($queryParameters);

        $this->assertEquals([
            'created' => false,
            'title'   => true,
            'updated' => true,
        ], $this->iterableToArray($parser->getSorts()));
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludesEmptyValue(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => 'comments,      ,comments.author',
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getIncludes());
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludesNotString1(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => ['not string'],
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getIncludes());
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludesNotString2(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => null,
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getIncludes());
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludesEmptyString1(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => '',
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getIncludes());
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludesEmptyString2(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_INCLUDE => '  ',
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getIncludes());
    }

    /**
     * Test query.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidFields(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_FIELDS => 'not array',
        ];

        $this->iterableToArray($this->createParser($queryParameters)->getFields());
    }

    /**
     * Shows how to integrate base query parser with EncodingParameters.
     *
     * @return void
     *
     * @see https://github.com/neomerx/json-api/issues/198
     */
    public function testIntegrationWithEncodingParameters(): void
    {
        $queryParameters = [
            BaseQueryParser::PARAM_FIELDS  => [
                'comments' => Comment::LINK_AUTHOR . ',     ' . Comment::ATTRIBUTE_BODY . '      ',
                'people'   => Author::ATTRIBUTE_FIRST_NAME,
            ],
            BaseQueryParser::PARAM_SORT    => '-created,title,+updated',
            BaseQueryParser::PARAM_INCLUDE => Comment::LINK_AUTHOR . ',   ' .
                Comment::LINK_AUTHOR . '.' . Author::LINK_COMMENTS,
        ];

        // It is expected that classes that encapsulate/extend BaseQueryParser would add features
        // such filters/pagination parsing, validation, etc. Though for simplicity we omit adding
        // them here and check how it integrates with EncodingParameters.
        $parser = new class ($queryParameters) extends BaseQueryParser
        {
            /**
             * @var null|array
             */
            private $fields = null;

            /**
             * @var null|array
             */
            private $sorts = null;

            /**
             * @var null|array
             */
            private $includes = null;

            /**
             * @return array
             */
            public function getFields(): array
            {
                if ($this->fields === null) {
                    $this->fields = $this->iterableToArray(parent::getFields());
                }

                return $this->fields;
            }

            /**
             * @return array
             */
            public function getSorts(): array
            {
                if ($this->sorts === null) {
                    $this->sorts = $this->iterableToArray(parent::getSorts());
                }

                return $this->sorts;
            }

            /**
             * @return array
             */
            public function getIncludes(): array
            {
                if ($this->includes === null) {
                    $this->includes = array_keys($this->iterableToArray(parent::getIncludes()));
                }

                return $this->includes;
            }

            /**
             * @param iterable $iterable
             *
             * @return array
             */
            private function iterableToArray(iterable $iterable): array
            {
                $result = [];

                foreach ($iterable as $key => $value) {
                    $result[$key] = $value instanceof Generator ? $this->iterableToArray($value) : $value;
                }

                return $result;
            }
        };

        // Check parsing works fine
        $this->assertSame([
            'comments' => [Comment::LINK_AUTHOR, Comment::ATTRIBUTE_BODY],
            'people'   => [Author::ATTRIBUTE_FIRST_NAME],
        ], $parser->getFields());
        $this->assertSame([
            'created' => false,
            'title'   => true,
            'updated' => true,
        ], $parser->getSorts());
        $this->assertSame([
            Comment::LINK_AUTHOR,
            Comment::LINK_AUTHOR . '.' . Author::LINK_COMMENTS,
        ], $parser->getIncludes());

        //
        // Now the main purpose of the test. Will it work with EncodingParameters?
        //

        // firstly setup some data
        $author                          = Author::instance(9, 'Dan', 'Gebhardt');
        $comments                        = [
            Comment::instance(5, 'First!', $author),
            Comment::instance(12, 'I like XML better', $author),
        ];
        $author->{Author::LINK_COMMENTS} = $comments;

        // and encode with params taken from the parser
        $encodingParams = new EncodingParameters($parser->getIncludes(), $parser->getFields());
        $actual         = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
        ])->encodeData($comments, $encodingParams);

        $expected = <<<EOL
        {
          "data": [
            {
              "type": "comments",
              "id": "5",
              "attributes": {
                "body": "First!"
              },
              "relationships": {
                "author": {
                  "data": { "type": "people", "id": "9" }
                }
              },
              "links": {
                "self": "/comments/5"
              }
            },
            {
              "type": "comments",
              "id": "12",
              "attributes": {
                "body": "I like XML better"
              },
              "relationships": {
                "author": {
                  "data": { "type": "people", "id": "9" }
                }
              },
              "links": {
                "self": "/comments/12"
              }
            }
          ],
          "included": [
            {
              "type": "people",
              "id": "9",
              "attributes":{
                "first_name":"Dan"
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
     * @param array $queryParameters
     *
     * @return BaseQueryParserInterface
     */
    private function createParser(array $queryParameters): BaseQueryParserInterface
    {
        return new BaseQueryParser($queryParameters);
    }

    /**
     * @param iterable $iterable
     *
     * @return array
     */
    private function iterableToArray(iterable $iterable): array
    {
        $result = [];

        foreach ($iterable as $key => $value) {
            $result[$key] = $value instanceof Generator ? $this->iterableToArray($value) : $value;
        }

        return $result;
    }
}
