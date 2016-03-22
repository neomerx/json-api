<?php namespace Neomerx\Tests\JsonApi\Http\Parameters;

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
use \Neomerx\JsonApi\Http\Request;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersParserInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParameterParserTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /** Header name */
    const HEADER_ACCEPT = HeaderInterface::HEADER_ACCEPT;

    /** Header name */
    const HEADER_CONTENT_TYPE = HeaderInterface::HEADER_CONTENT_TYPE;

    /** Query params */
    const QUERY_PARAMS = 'query_params';

    /**
     * @var ParametersParserInterface
     */
    private $parser;

    /**
     * @var array
     */
    private $expectedCalls = [];

    /**
     * @var array
     */
    private $actrualCalls = [];

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser = (new Factory())->createParametersParser();

        $this->expectedCalls = $this->actrualCalls = [
            self::HEADER_ACCEPT       => 0,
            self::HEADER_CONTENT_TYPE => 0,
            self::QUERY_PARAMS        => 0,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->assertEquals($this->expectedCalls, $this->actrualCalls);
    }

    /**
     * Test parse parameters.
     */
    public function testHeadersWithNoExtensionsAndParameters()
    {
        $parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, []));

        $this->assertCount(1, $parameters->getContentTypeHeader()->getMediaTypes());
        $this->assertNotNull($contentType = $parameters->getContentTypeHeader()->getMediaTypes()[0]);
        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertCount(1, $parameters->getAcceptHeader()->getMediaTypes());
        $this->assertNotNull($accept = $parameters->getAcceptHeader()->getMediaTypes()[0]);
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertNull($contentType->getParameters());
        $this->assertNull($accept->getParameters());

        $this->assertNull($parameters->getFieldSets());
        $this->assertNull($parameters->getIncludePaths());
        $this->assertNull($parameters->getSortParameters());
        $this->assertNull($parameters->getFilteringParameters());
        $this->assertNull($parameters->getPaginationParameters());

        $this->assertTrue($parameters->isEmpty());
    }

    /**
     * Test parse parameters.
     */
    public function testHeadersWithNoExtensionsButWithParameters()
    {
        $fieldSets = ['type1' => 'fields1,fields2'];
        $filter    = ['some' => 'filter'];
        $paging    = ['size' => 10, 'offset' => 4];

        $input = [
            'fields'  => $fieldSets,
            'include' => 'author,comments,comments.author',
            'sort'    => '-created,+title,name', // omitting '+' is against spec but in reality frameworks remove it
            'filter'  => $filter,
            'page'    => $paging,
        ];

        $parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input));

        $this->assertEquals(['type1' => ['fields1', 'fields2']], $parameters->getFieldSets());
        $this->assertEquals(['author' , 'comments', 'comments.author'], $parameters->getIncludePaths());

        $this->assertCount(3, $sortParams = $parameters->getSortParameters());
        $this->assertEquals('created', $sortParams[0]->getField());
        $this->assertEquals('title', $sortParams[1]->getField());
        $this->assertEquals('name', $sortParams[2]->getField());
        $this->assertFalse($sortParams[0]->isAscending());
        $this->assertTrue($sortParams[1]->isAscending());
        $this->assertTrue($sortParams[2]->isAscending());
        $this->assertTrue($sortParams[0]->isDescending());
        $this->assertFalse($sortParams[1]->isDescending());
        $this->assertFalse($sortParams[2]->isDescending());

        $this->assertEquals($filter, $parameters->getFilteringParameters());
        $this->assertEquals($paging, $parameters->getPaginationParameters());
    }

    /**
     * Test miss field in sort params.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testSendIncorrectSortParams()
    {
        $input = [
            'sort' => '-created,,name'
        ];
        $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input));
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidPageParams()
    {
        $input = [
            'page' => '2',
        ];
        $this->assertNotNull($parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input)));
        $this->assertNull($parameters->getPaginationParameters());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidFilterParams()
    {
        $input = [
            'filter' => 'whatever',
        ];
        $this->assertNotNull($parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input)));

        $this->assertNull($parameters->getSortParameters());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidIncludeParams()
    {
        $input = [
            'include' => ['whatever'],
        ];
        $this->assertNotNull($parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input)));

        $this->assertNull($parameters->getIncludePaths());
    }

    /**
     * Test invalid params.
     *
     * Issue #58 @see https://github.com/neomerx/json-api/issues/58
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidSortParams()
    {
        $input = [
            'sort' => ['whatever'],
        ];
        $this->assertNotNull($parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input)));

        $this->assertNull($parameters->getIncludePaths());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersNoParams()
    {
        $parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE . ';', []));

        $this->assertEquals(self::TYPE, $parameters->getContentTypeHeader()->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getAcceptHeader()->getMediaTypes()[0]->getMediaType());
        $this->assertNull($parameters->getContentTypeHeader()->getMediaTypes()[0]->getParameters());
        $this->assertNull($parameters->getAcceptHeader()->getMediaTypes()[0]->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsNoExtraParams()
    {
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE . ';ext="ext1,ext2"', self::TYPE . ';ext=ext1', [])
        );

        $contentType = $parameters->getContentTypeHeader();
        $accept = $parameters->getAcceptHeader();

        $this->assertEquals(self::TYPE, $contentType->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(self::TYPE, $accept->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(['ext' => 'ext1,ext2'], $contentType->getMediaTypes()[0]->getParameters());
        $this->assertEquals(['ext' => 'ext1'], $accept->getMediaTypes()[0]->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsWithExtraParams()
    {
        $parameters = $this->parser->parse($this->prepareRequest(
            self::TYPE . ' ;  boo = foo; ext="ext1,ext2";  foo = boo ',
            self::TYPE . ' ; boo = foo; ext=ext1;  foo = boo',
            []
        ));

        $contentType = $parameters->getContentTypeHeader();
        $accept = $parameters->getAcceptHeader();

        $this->assertEquals(self::TYPE, $contentType->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(self::TYPE, $accept->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1,ext2', 'foo' => 'boo'],
            $contentType->getMediaTypes()[0]->getParameters()
        );
        $this->assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1', 'foo' => 'boo'],
            $accept->getMediaTypes()[0]->getParameters()
        );
    }

    /**
     * Test parsing unrecognized parameters.
     */
    public function testUnrecognizedParameters()
    {
        $input = [
            'include'      => 'author,comments,comments.author',
            'unrecognized' => ['parameters'],
        ];
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE . ';ext="ext1,ext2"', self::TYPE . ';ext=ext1', $input)
        );

        $this->assertEquals(['unrecognized' => ['parameters']], $parameters->getUnrecognizedParameters());
    }

    /**
     * Test parse headers when 'Accept' header is not given.
     */
    public function testParseWithEmptyAcceptHeader()
    {
        $parameters = $this->parser->parse($this->prepareRequest(self::TYPE, '', []));

        $accept = $parameters->getAcceptHeader();
        $this->assertCount(1, $accept->getMediaTypes());
        $this->assertEquals(self::TYPE, $accept->getMediaTypes()[0]->getMediaType());
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testParseIvalidHeaders1()
    {
        $this->parser->parse($this->prepareRequest(self::TYPE.';foo', self::TYPE, [], 1, 0, 0));
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testParseIvalidHeaders2()
    {
        $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE.';foo', [], 1, 1, 0));
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts]=
     *
     * @see https://github.com/neomerx/json-api/issues/107
     */
    public function testFieldSetWithEmptyField()
    {
        $input = [
            'fields' => ['type1' => 'fields1,fields2', 'type2' => '']
        ];
        $result = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input));

        // note type2 has empty field set
        $this->assertEquals(['type1' => ['fields1', 'fields2'], 'type2' => []], $result->getFieldSets());
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts][foo]=title
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testInvalidFieldSetWithMultiDimensionArray()
    {
        $input = [
            'fields' => ['type' => ['subtype' => 'fields1,fields2']]
        ];
        $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input));
    }

    /**
     * Test that unrecognized parameters are ignored when calling `isEmpty` - issue #89
     *
     * @author https://github.com/lindyhopchris
     */
    public function testUnrecognizedParametersIgnoredByIsEmpty()
    {
        $input = [
            'unrecognized' => 'foo',
        ];

        $parameters = $this->parser->parse($this->prepareRequest(self::TYPE, self::TYPE, $input));

        $this->assertTrue($parameters->isEmpty());
        $this->assertEquals(['unrecognized' => 'foo'], $parameters->getUnrecognizedParameters());
    }

    /**
     * @param string $contentType
     * @param string $accept
     * @param array  $input
     * @param int    $contentTypeTimes
     * @param int    $acceptTimes
     * @param int    $parametersTimes
     *
     * @return ServerRequestInterface
     */
    private function prepareRequest(
        $contentType,
        $accept,
        array $input,
        $contentTypeTimes = 1,
        $acceptTimes = 1,
        $parametersTimes = 1
    ) {
        $request = new Request(function ($name) use ($accept, $contentType) {
            $headers = [
                self::HEADER_ACCEPT       => empty($accept) === true ? [] : [$accept],
                self::HEADER_CONTENT_TYPE => empty($contentType) === true ? [] : [$contentType],
            ];

            $this->actrualCalls[$name]++;

            return $headers[$name];
        }, function () use ($input) {
            $this->actrualCalls[self::QUERY_PARAMS]++;
            return $input;
        });

        $this->expectedCalls[self::HEADER_ACCEPT]       = $acceptTimes;
        $this->expectedCalls[self::HEADER_CONTENT_TYPE] = $contentTypeTimes;
        $this->expectedCalls[self::QUERY_PARAMS]        = $parametersTimes;

        return $request;
    }
}
