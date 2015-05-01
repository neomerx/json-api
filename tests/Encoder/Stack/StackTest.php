<?php namespace Neomerx\Tests\JsonApi\Encoder\Stack;

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
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Encoder\Factory\EncoderFactory;
use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class StackTest extends BaseTestCase
{
    /**
     * @var StackInterface
     */
    private $stack;

    /**
     * @var LinkObjectInterface
     */
    private $mockLinkObject;

    /**
     * @var ResourceObjectInterface
     */
    private $mockResourceObject;

    /**
     * Set up test.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->stack              = (new EncoderFactory())->createStack();
        $this->mockLinkObject     = Mockery::mock(LinkObjectInterface::class);
        $this->mockResourceObject = Mockery::mock(ResourceObjectInterface::class);

        $this->mockLinkObject->shouldReceive('getName')->zeroOrMoreTimes()->withAnyArgs()->andReturn('someName');
        $this->mockLinkObject->shouldReceive('isShouldBeIncluded')->zeroOrMoreTimes()->withAnyArgs()->andReturn(true);
    }

    /**
     * Test empty stack.
     */
    public function testEmptyStack()
    {
        $this->assertEquals(0, $this->stack->count());
        $this->assertNull($this->stack->end());
        $this->stack->pop();
        $this->assertNull($this->stack->end());
        $this->assertNull($this->stack->end(1));

        $checkEmpty = true;
        foreach ($this->stack as $frame) {
            $frame ?: null;
            $checkEmpty = false;
        }
        $this->assertTrue($checkEmpty);
    }

    /**
     * Test push.
     */
    public function testPush()
    {
        $this->stack->push();
        $this->assertEquals(1, $this->stack->count());
        $this->assertEquals(1, $this->stack->end()->getLevel());
        $this->assertNull($this->stack->end()->getResourceObject());
        $this->assertNull($this->stack->end()->getLinkObject());

        $this->stack->setCurrentResourceObject($this->mockResourceObject);
        $this->assertSame($this->mockResourceObject, $this->stack->end()->getResourceObject());
        $this->assertNull($this->stack->end(1));
        $this->assertNull($this->stack->end(2));

        $frame2 = $this->stack->push();
        $this->stack->setCurrentLinkObject($this->mockLinkObject);
        $this->assertSame($this->mockLinkObject, $this->stack->end()->getLinkObject());
        $this->assertEquals(2, $this->stack->count());
        $this->assertEquals(2, $this->stack->end()->getLevel());
        $this->assertSame($frame2, $this->stack->end());
        $this->assertNull($this->stack->end()->getResourceObject());
        $this->assertSame($this->mockLinkObject, $this->stack->end()->getLinkObject());
        $this->assertNull($this->stack->end(2));
    }

    /**
     * Test pop.
     */
    public function testPop()
    {
        $this->assertNotNull($frame1 = $this->stack->push());
        $this->assertSame($frame1, $this->stack->end());
        $this->stack->pop();
        $this->assertNull($this->stack->end());
        $this->assertEquals(0, $this->stack->count());

        $this->assertNotNull($frame1 = $this->stack->push());
        $this->assertNotNull($frame2 = $this->stack->push());
        $this->assertSame($frame2, $this->stack->end());
        $this->assertSame($frame1, $this->stack->end(1));
        $this->assertNull($this->stack->end(2));

        $this->stack->pop();
        $this->assertSame($frame1, $this->stack->end());
        $this->assertNull($this->stack->end(1));

        $this->stack->pop();
        $this->assertEquals(0, $this->stack->count());
        $this->assertNull($this->stack->end());
    }
}
