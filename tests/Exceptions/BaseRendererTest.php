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
use \Mockery\MockInterface;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\BaseRenderer;
use \Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class BaseRendererTest extends BaseTestCase
{
    /**
     * @var BaseRenderer
     */
    private $renderer;

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

        $this->mockResponses = Mockery::mock(ResponsesInterface::class);
        /** @var ResponsesInterface $responses */
        $responses = $this->mockResponses;

        $this->renderer = Mockery::mock(BaseRenderer::class, [$responses])->makePartial();
    }

    /**
     * Test withXXX and render methods.
     */
    public function testRender()
    {
        $statusCode   = 123;
        $headers      = ['some' => 'headers'];
        /** @var MediaTypeInterface $mediaType */
        $mediaType    = Mockery::mock(MediaTypeInterface::class);
        /** @var SupportedExtensionsInterface $supportedExt */
        $supportedExt = Mockery::mock(SupportedExtensionsInterface::class);
        $exception    = new Exception();
        $content      = 'response content';

        /** @var MockInterface $renderer */
        $renderer = $this->renderer;
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $renderer->shouldReceive('getContent')->once()->with($exception)->andReturn($content);

        $response = 'response with headers, body, status';
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockResponses->shouldReceive('getResponse')->once()->withArgs([
            $statusCode,
            $mediaType,
            $content,
            $supportedExt,
            $headers
        ])->andReturn($response);

        $rendererResponse = $this->renderer
            ->withHeaders($headers)
            ->withMediaType($mediaType)
            ->withStatusCode($statusCode)
            ->withSupportedExtensions($supportedExt)
            ->render($exception);
        $this->assertEquals($response, $rendererResponse);
    }
}
