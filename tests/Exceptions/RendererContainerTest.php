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
use \InvalidArgumentException;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\RendererContainer;
use \Neomerx\JsonApi\Contracts\Exceptions\RendererInterface;
use \Neomerx\JsonApi\Contracts\Exceptions\RendererContainerInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RendererContainerTest extends BaseTestCase
{
    /**
     * @var RendererContainerInterface
     */
    private $container;

    /**
     * @var MockInterface
     */
    private $mockDefaultRenderer;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockDefaultRenderer = Mockery::mock(RendererInterface::class);
        /** @var RendererInterface $defaultRenderer */
        $defaultRenderer = $this->mockDefaultRenderer;

        $this->container = new RendererContainer($defaultRenderer);
    }

    /**
     * Test get render for unknown exception.
     */
    public function testGetRenderForUnknownException()
    {
        // we haven't registered any renders yet so any exception will be unknown

        $renderer = $this->container->getRenderer(Exception::class);
        $this->assertInstanceOf(RendererInterface::class, $renderer);
        $this->assertSame($this->mockDefaultRenderer, $renderer);
    }

    /**
     * Test get render for known exception.
     */
    public function testGetRenderForKnownException()
    {
        /** @var RendererInterface $mockRenderer */
        $mockRenderer = Mockery::mock(RendererInterface::class);

        /** @var RendererInterface $mockRenderer */
        $this->container->registerRenderer(InvalidArgumentException::class, $mockRenderer);
        $this->assertSame($mockRenderer, $this->container->getRenderer(InvalidArgumentException::class));

        // renders for unknown exceptions should work as well
        $this->assertSame($this->mockDefaultRenderer, $this->container->getRenderer(Exception::class));
    }
}
