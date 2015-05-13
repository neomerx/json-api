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
use \Neomerx\JsonApi\Contracts\Codec\CodecContainerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParameterParserTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = CodecContainerInterface::JSON_API_TYPE;

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

        $this->assertEquals(self::TYPE, $parameters->getInputMediaType()->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getOutputMediaType()->getMediaType());
        $this->assertEquals(MediaTypeInterface::NO_EXT, $parameters->getInputMediaType()->getExtensions());
        $this->assertEquals(MediaTypeInterface::NO_EXT, $parameters->getOutputMediaType()->getExtensions());

        $this->assertNull($parameters->getFieldSets());
        $this->assertNull($parameters->getIncludePaths());
        $this->assertNull($parameters->getSortParameters());
        $this->assertNull($parameters->getFilteringParameters());
        $this->assertNull($parameters->getPaginationParameters());
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

        $this->assertEquals(self::TYPE, $parameters->getInputMediaType()->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getOutputMediaType()->getMediaType());
        $this->assertEquals(MediaTypeInterface::NO_EXT, $parameters->getInputMediaType()->getExtensions());
        $this->assertEquals(MediaTypeInterface::NO_EXT, $parameters->getOutputMediaType()->getExtensions());
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

        $this->assertEquals(self::TYPE, $parameters->getInputMediaType()->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getOutputMediaType()->getMediaType());
        $this->assertEquals('ext1,ext2', $parameters->getInputMediaType()->getExtensions());
        $this->assertEquals('ext1', $parameters->getOutputMediaType()->getExtensions());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsWithExtraParams()
    {
        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::TYPE . ' ;  boo = foo, ext="ext1,ext2",  boo = foo ',
                self::TYPE . ' ; boo = foo, ext=ext1,  boo = foo',
                []
            ),
            $this->prepareExceptions()
        );

        $this->assertEquals(self::TYPE, $parameters->getInputMediaType()->getMediaType());
        $this->assertEquals(self::TYPE, $parameters->getOutputMediaType()->getMediaType());
        $this->assertEquals('ext1', $parameters->getOutputMediaType()->getExtensions());
        $this->assertEquals('ext1,ext2', $parameters->getInputMediaType()->getExtensions());
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
     * @param string $contentType
     * @param string $accept
     * @param array  $input
     *
     * @return CurrentRequestInterface
     */
    private function prepareRequest($contentType, $accept, array $input)
    {
        $this->mockRequest->shouldReceive('getHeader')->with('Content-Type')->once()->andReturn($contentType);
        $this->mockRequest->shouldReceive('getHeader')->with('Accept')->once()->andReturn($accept);
        $this->mockRequest->shouldReceive('getQueryParameters')->withNoArgs()->once()->andReturn($input);

        /** @var CurrentRequestInterface $request */
        $request = $this->mockRequest;

        return $request;
    }

    /**
     * @param string $exceptionMethod
     *
     * @return ExceptionThrowerInterface
     */
    private function prepareExceptions($exceptionMethod = null)
    {
        if ($exceptionMethod !== null) {
            $this->mockThrower->shouldReceive($exceptionMethod)->atLeast(1)->withNoArgs()->andReturnUndefined();
        }

        /** @var ExceptionThrowerInterface $exceptions */
        $exceptions = $this->mockThrower;

        return $exceptions;
    }
}
