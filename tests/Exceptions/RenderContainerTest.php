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

use \Exception;
use \LogicException;
use \InvalidArgumentException;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Exceptions\RenderContainer;
use \Neomerx\JsonApi\Contracts\Exceptions\RenderContainerInterface;

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
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $responseClosure = function ($statusCode) {
            return 'error: '. $statusCode;
        };

        $this->container = new RenderContainer($responseClosure, self::DEFAULT_CODE);
    }

    /**
     * Test get render for unknown exception.
     */
    public function testGetRenderForUnknownException()
    {
        // we haven't registered any renders yet so any exception will be unknown

        $this->assertNotNull($render = $this->container->getRender(new Exception()));
        $this->assertEquals('error: '. self::DEFAULT_CODE, $render());
    }

    /**
     * Test get render for known exception.
     */
    public function testGetRenderForKnownException()
    {
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
    public function testRegisterMapping()
    {
        $this->container->registerMapping([
            InvalidArgumentException::class => 123,
            LogicException::class           => 456,
        ]);

        $this->assertNotNull($render = $this->container->getRender(new InvalidArgumentException()));
        $this->assertEquals('error: '. 123, $render());

        $this->assertNotNull($render = $this->container->getRender(new LogicException()));
        $this->assertEquals('error: '. 456, $render());

        $this->assertNotNull($render = $this->container->getRender(new Exception()));
        $this->assertEquals('error: '. self::DEFAULT_CODE, $render());
    }
}
