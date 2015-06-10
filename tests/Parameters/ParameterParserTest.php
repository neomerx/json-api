<?php namespace Neomerx\Tests\JsonApi\Parameters;

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
use \Mockery\MockInterface;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Parameters\ParametersFactory;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParameterParserTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /**
     * @var ParametersParserInterface
     */
    private $parser;

    /**
     * @var MockInterface
     */
    private $mockRequest;

    /**
     * @var MockInterface
     */
    private $mockThrower;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser      = (new ParametersFactory())->createParametersParser();
        $this->mockRequest = Mockery::mock(CurrentRequestInterface::class);
        $this->mockThrower = Mockery::mock(ExceptionThrowerInterface::class);
    }

    /**
     * Test parse parameters.
     */
    public function testHeadersWithNoExtensionsAndParameters()
    {
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, []),
            $this->prepareExceptions()
        );

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

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $input),
            $this->prepareExceptions()
        );

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
     * @expectedException \Exception
     */
    public function testSendIncorrectSortParams()
    {
        $input = [
            'sort' => '-created,,name'
        ];
        $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $input),
            $this->prepareExceptions('throwBadRequest')
        );
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersNoParams()
    {
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE . ';', []),
            $this->prepareExceptions()
        );

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
            $this->prepareRequest(self::TYPE . ';ext="ext1,ext2"', self::TYPE . ';ext=ext1', []),
            $this->prepareExceptions()
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
        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::TYPE . ' ;  boo = foo; ext="ext1,ext2";  foo = boo ',
                self::TYPE . ' ; boo = foo; ext=ext1;  foo = boo',
                []
            ),
            $this->prepareExceptions()
        );

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
            $this->prepareRequest(self::TYPE . ';ext="ext1,ext2"', self::TYPE . ';ext=ext1', $input),
            $this->prepareExceptions()
        );

        $this->assertEquals(['unrecognized' => ['parameters']], $parameters->getUnrecognizedParameters());
    }

    /**
     * Test parse headers when 'Accept' header is not given.
     */
    public function testParseWithEmptyAcceptHeader()
    {
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, '', []),
            $this->prepareExceptions()
        );

        $accept = $parameters->getAcceptHeader();
        $this->assertCount(1, $accept->getMediaTypes());
        $this->assertEquals(self::TYPE, $accept->getMediaTypes()[0]->getMediaType());
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException \Exception
     */
    public function testParseIvalidHeaders1()
    {
        $this->parser->parse(
            $this->prepareRequest(self::TYPE.';foo', self::TYPE, [], 1, 0, 0),
            $this->prepareExceptions('throwBadRequest')
        );
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException \Exception
     */
    public function testParseIvalidHeaders2()
    {
        $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE.';foo', [], 1, 1, 0),
            $this->prepareExceptions('throwBadRequest')
        );
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts]=
     */
    public function testFieldSetWithEmptyField()
    {
        $input = [
            'fields' => ['type1' => 'fields1,fields2', 'type2' => '']
        ];
        $result = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $input),
            $this->prepareExceptions()
        );

        // note type2 was ignored
        $this->assertEquals(['type1' => ['fields1', 'fields2']], $result->getFieldSets());
    }

    /**
     * Test miss field in sort params. Sample /posts/1?fields[posts][foo]=title
     *
     * @expectedException \Exception
     */
    public function testInvalidFieldSetWithMultiDimensionArray()
    {
        $input = [
            'fields' => ['type' => ['subtype' => 'fields1,fields2']]
        ];
        $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $input),
            $this->prepareExceptions('throwBadRequest')
        );
    }

    /**
     * @param string $contentType
     * @param string $accept
     * @param array  $input
     * @param int    $contentTypeTimes
     * @param int    $acceptTimes
     * @param int    $parametersTimes
     *
     * @return CurrentRequestInterface
     */
    private function prepareRequest(
        $contentType,
        $accept,
        array $input,
        $contentTypeTimes = 1,
        $acceptTimes = 1,
        $parametersTimes = 1
    ) {
        $this->mockRequest->shouldReceive('getHeader')->with('Content-Type')
            ->times($contentTypeTimes)->andReturn($contentType);
        $this->mockRequest->shouldReceive('getHeader')->with('Accept')->times($acceptTimes)->andReturn($accept);
        $this->mockRequest->shouldReceive('getQueryParameters')
            ->withNoArgs()->times($parametersTimes)->andReturn($input);

        /** @var CurrentRequestInterface $request */
        $request = $this->mockRequest;

        return $request;
    }

    /**
     * @param string $exceptionMethod
     * @param int    $times
     *
     * @return ExceptionThrowerInterface
     */
    private function prepareExceptions($exceptionMethod = null, $times = 1)
    {
        if ($exceptionMethod !== null) {
            $this->mockThrower->shouldReceive($exceptionMethod)
                ->times($times)->withNoArgs()->andThrow(new \Exception());
        }

        /** @var ExceptionThrowerInterface $exceptions */
        $exceptions = $this->mockThrower;

        return $exceptions;
    }
}
