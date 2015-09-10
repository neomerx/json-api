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
use Neomerx\JsonApi\Exceptions\ThrowableError;
use \Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Responses\Responses;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\RenderContainer;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;
use \Neomerx\JsonApi\Contracts\Integration\NativeResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;
use Neomerx\JsonApi\Contracts\Exceptions\Renderer\ExceptionRendererInterface;

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
     * @var MockInterface
     */
    private $mockSupportedExtensions;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockSupportedExtensions = Mockery::mock(SupportedExtensionsInterface::class);
        $mockSupportedExtensions = $this->mockSupportedExtensions;
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $mockSupportedExtensions->shouldReceive('getExtensions')->zeroOrMoreTimes()->withNoArgs()->andReturn([]);
        $extensionsClosure = function () use ($mockSupportedExtensions) {
            return $mockSupportedExtensions;
        };

        $this->mockResponses = Mockery::mock(NativeResponsesInterface::class);

        /** @var NativeResponsesInterface $mockResponses */
        $mockResponses = $this->mockResponses;

        $this->container = new RenderContainer(new Responses($mockResponses), $extensionsClosure, self::DEFAULT_CODE);
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

        $renderer = $this->container->getRenderer(new Exception());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $this->assertEquals('error: '. self::DEFAULT_CODE, $renderer->render(new Exception()));
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

        $mockRenderer = Mockery::mock(ExceptionRendererInterface::class);
        $mockRenderer->shouldReceive('withSupportedExtensions')->once()
            ->withArgs([$this->mockSupportedExtensions])
            ->andReturnSelf();

        /** @var ExceptionRendererInterface $mockRenderer */
        $this->container->registerRenderer(InvalidArgumentException::class, $mockRenderer);
        $this->assertSame($mockRenderer, $this->container->getRenderer(new InvalidArgumentException()));

        // renders for unknown exceptions should work as well
        $renderer = $this->container->getRenderer(new Exception());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $this->assertNotSame($mockRenderer, $renderer);
        $this->assertEquals('error: ' . self::DEFAULT_CODE, $renderer->render(new InvalidArgumentException()));
    }

    /**
     * Test register exception mapping to status codes.
     */
    public function testRegisterHttpCodeMapping()
    {
        $this->container->registerHttpCodeMapping([
            InvalidArgumentException::class => 418,
            LogicException::class           => 456,
        ]);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, 418, Mockery::any()])
            ->andReturn('error: '. 418);
        $renderer = $this->container->getRenderer(new InvalidArgumentException());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $this->assertEquals('error: '. 418, $renderer->render(new InvalidArgumentException()));

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, 456, Mockery::any()])
            ->andReturn('error: '. 456);
        $renderer = $this->container->getRenderer(new LogicException());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $this->assertEquals('error: '. 456, $renderer->render(new LogicException()));

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([null, self::DEFAULT_CODE, Mockery::any()])
            ->andReturn('error: '. self::DEFAULT_CODE);
        $renderer = $this->container->getRenderer(new Exception());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $this->assertEquals('error: '. self::DEFAULT_CODE, $renderer->render(new Exception()));
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

        $customHttpCode = 418;
        $this->container->registerJsonApiErrorMapping([
            ThrowableError::class => $customHttpCode,
        ]);

        $title = 'Error title';
        $error = new ThrowableError(null, null, null, null, $title);
        $errorDocument = Encoder::instance([])->encodeError($error);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('createResponse')->once()
            ->withArgs([Mockery::type('string'), $customHttpCode, $expectedHeaders])
            ->andReturn($errorDocument);
        $renderer = $this->container->getRenderer($error);
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);

        // let's assume our exception can provide JSON API Error information somehow.
        $actual = $renderer->withHeaders($headers)->render($error);
        $this->assertEquals($errorDocument, $actual);
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

        $renderer = $this->container->getRenderer(new InvalidArgumentException());
        $this->assertInstanceOf(ExceptionRendererInterface::class, $renderer);
        $actual = $renderer->withHeaders($headers)->render(new InvalidArgumentException());
        $this->assertEquals($response, $actual);
    }
}
