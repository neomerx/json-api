<?php namespace Neomerx\Tests\JsonApi\Exceptions;

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
use \Exception;
use \LogicException;
use \Mockery\MockInterface;
use \InvalidArgumentException;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\RenderContainer;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;
use \Neomerx\JsonApi\Contracts\Integration\NativeResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RenderContainerTest extends BaseTestCase
{
    /** Default error code */
    const DEFAULT_CODE = 567;

    /**
     * @var RenderContainerInterface
     */
    private $container;

    /**
     * @var MockInterface
     */
    private $mockResponses;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $mockSupportedExtensions = Mockery::mock(SupportedExtensionsInterface::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockSupportedExtensions->shouldReceive('getExtensions')->zeroOrMoreTimes()->withNoArgs()->andReturn([]);
        $extensionsClosure = function () use ($mockSupportedExtensions) {
            return $mockSupportedExtensions;
        };

        $this->mockResponses = Mockery::mock(NativeResponsesInterface::class);

        /** @var NativeResponsesInterface $mockResponses */
        $mockResponses = $this->mockResponses;

        $this->container = new RenderContainer(new Factory(), $mockResponses, $extensionsClosure, self::DEFAULT_CODE);
    }

    /**
     * Test get render for unknown exception.
     */
    public function testGetRenderForUnknownException()
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, self::DEFAULT_CODE, Mockery::any()])
            ->andReturn('error: '. self::DEFAULT_CODE);

        // we haven't registered any renders yet so any exception will be unknown

        $this->assertNotNull($render = $this->container->getRender(new Exception()));
        $this->assertEquals('error: '. self::DEFAULT_CODE, $render());
    }

    /**
     * Test get render for known exception.
     */
    public function testGetRenderForKnownException()
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, self::DEFAULT_CODE, Mockery::any()])
            ->andReturn('error: '. self::DEFAULT_CODE);

        $customRender = function ($arg1, $arg2, $arg3) {
            return $arg1 . ' ' . $arg2 . ' ' . $arg3;
        };
        $this->container->registerRender(InvalidArgumentException::class, $customRender);
        $this->assertNotNull($render = $this->container->getRender(new InvalidArgumentException()));
        $this->assertEquals('I am a custom render', $render('I am', 'a custom', 'render'));

        // renders for unknown exceptions should work as well
        $this->assertNotNull($render = $this->container->getRender(new Exception()));
        $this->assertEquals('error: '. self::DEFAULT_CODE, $render());
    }

    /**
     * Test register exception mapping to status codes.
     */
    public function testRegisterHttpCodeMapping()
    {
        $this->container->registerHttpCodeMapping([
            InvalidArgumentException::class => 123,
            LogicException::class           => 456,
        ]);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, 123, Mockery::any()])
            ->andReturn('error: '. 123);
        $this->assertNotNull($render = $this->container->getRender(new InvalidArgumentException()));
        $this->assertEquals('error: '. 123, $render());

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, 456, Mockery::any()])
            ->andReturn('error: '. 456);
        $this->assertNotNull($render = $this->container->getRender(new LogicException()));
        $this->assertEquals('error: '. 456, $render());

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, self::DEFAULT_CODE, Mockery::any()])
            ->andReturn('error: '. self::DEFAULT_CODE);
        $this->assertNotNull($render = $this->container->getRender(new Exception()));
        $this->assertEquals('error: '. self::DEFAULT_CODE, $render());
    }

    /**
     * Test register exception mapping for JSON API Errors.
     */
    public function testRegisterJsonApiErrorMapping()
    {
        // lets check if render can work with headers
        // Issue #52 https://github.com/neomerx/json-api/issues/52
        $headers  = [
            'header' => 'value',
        ];
        $expectedHeaders = array_merge(
            $headers,
            [HeaderInterface::HEADER_CONTENT_TYPE => MediaTypeInterface::JSON_API_MEDIA_TYPE]
        );

        $customHttpCode = 123;
        $this->container->registerJsonApiErrorMapping([
            InvalidArgumentException::class => $customHttpCode,
        ]);

        $title = 'Error title';
        $error = new Error(null, null, null, null, $title);
        $errorDocument = Encoder::instance([])->error($error);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([Mockery::type('string'), $customHttpCode, $expectedHeaders])
            ->andReturn($errorDocument);
        $this->assertNotNull($render = $this->container->getRender(new InvalidArgumentException()));

        // let's assume our exception can provide JSON API Error information somehow.

        $this->assertEquals($errorDocument, $render([$error], null, $headers));
    }

    /**
     * Test render can add headers to responses.
     *
     * Issue #52 https://github.com/neomerx/json-api/issues/52
     */
    public function testDefaultHttpCodeRenderWithHeaders()
    {
        $response = 'some-response';
        $headers  = [
            'header' => 'value',
        ];

        $expectedHeaders = array_merge(
            $headers,
            [HeaderInterface::HEADER_CONTENT_TYPE => MediaTypeInterface::JSON_API_MEDIA_TYPE]
        );
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, self::DEFAULT_CODE, $expectedHeaders])
            ->andReturn($response);

        $this->assertNotNull($render = $this->container->getRender(new InvalidArgumentException()));
        $this->assertEquals($response, $render(null, null, $headers));
    }
}
