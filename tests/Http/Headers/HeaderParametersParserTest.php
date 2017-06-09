<?php namespace Neomerx\Tests\JsonApi\Http\Headers;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \LogicException;
use \Neomerx\JsonApi\Http\Request;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class HeaderParametersParserTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /** Header name */
    const HEADER_ACCEPT = HeaderInterface::HEADER_ACCEPT;

    /** Header name */
    const HEADER_CONTENT_TYPE = HeaderInterface::HEADER_CONTENT_TYPE;

    /**
     * @var HeaderParametersParserInterface
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

        $this->parser = (new Factory())->createHeaderParametersParser();

        $this->expectedCalls = $this->actrualCalls = [
            self::HEADER_ACCEPT       => 0,
            self::HEADER_CONTENT_TYPE => 0,
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
        $parameters = $this->parser->parse($this->prepareRequest('POST', self::TYPE, self::TYPE));

        $this->assertEquals('POST', $parameters->getMethod());
        $this->assertCount(1, $parameters->getContentTypeHeader()->getMediaTypes());
        $this->assertNotNull($contentType = $parameters->getContentTypeHeader()->getMediaTypes()[0]);
        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertCount(1, $parameters->getAcceptHeader()->getMediaTypes());
        $this->assertNotNull($accept = $parameters->getAcceptHeader()->getMediaTypes()[0]);
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertNull($contentType->getParameters());
        $this->assertNull($accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersNoParams()
    {
        $parameters = $this->parser->parse($this->prepareRequest('POST', self::TYPE, self::TYPE . ';'));

        $this->assertEquals(self::TYPE, $parameters->getContentTypeHeader()->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getAcceptHeader()->getMediaTypes()[0]->getMediaType());
        $this->assertNull($parameters->getContentTypeHeader()->getMediaTypes()[0]->getParameters());
        $this->assertNull($parameters->getAcceptHeader()->getMediaTypes()[0]->getParameters());
    }

    /**
     * Test parse headers. Issue #135
     *
     * @see https://github.com/neomerx/json-api/issues/135
     */
    public function testParseEmptyContentTypeHeader()
    {
        $parameters = $this->parser->parse($this->prepareRequest('POST', '', self::TYPE));

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
            $this->prepareRequest('POST', self::TYPE . ';ext="ext1,ext2"', self::TYPE . ';ext=ext1')
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
            'POST',
            self::TYPE . ' ;  boo = foo; ext="ext1,ext2";  foo = boo ',
            self::TYPE . ' ; boo = foo; ext=ext1;  foo = boo'
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
     * Test parse headers when 'Accept' header is not given.
     */
    public function testParseWithEmptyAcceptHeader()
    {
        $parameters = $this->parser->parse($this->prepareRequest('POST', self::TYPE, ''));

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
        $this->parser->parse($this->prepareRequest('POST', self::TYPE.';foo', self::TYPE, 1, 0));
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException \Neomerx\JsonApi\Exceptions\JsonApiException
     */
    public function testParseIvalidHeaders2()
    {
        $this->parser->parse($this->prepareRequest('POST', self::TYPE, self::TYPE.';foo', 1, 1));
    }

    /**
     * @param string $method
     * @param string $contentType
     * @param string $accept
     * @param int    $contentTypeTimes
     * @param int    $acceptTimes
     *
     * @return ServerRequestInterface
     */
    private function prepareRequest(
        $method,
        $contentType,
        $accept,
        $contentTypeTimes = 1,
        $acceptTimes = 1
    ) {
        $request = new Request(function () use ($method) {
            return $method;
        }, function ($name) use ($accept, $contentType) {
            $headers = [
                self::HEADER_ACCEPT       => $accept === null ? [] : [$accept],
                self::HEADER_CONTENT_TYPE => $contentType === null ? [] : [$contentType],
            ];

            $this->actrualCalls[$name]++;

            return $headers[$name];
        }, function () {
            throw new LogicException();
        });

        $this->expectedCalls[self::HEADER_ACCEPT]       = $acceptTimes;
        $this->expectedCalls[self::HEADER_CONTENT_TYPE] = $contentTypeTimes;

        return $request;
    }
}
