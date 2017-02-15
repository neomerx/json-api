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

use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Http\Headers\Header;

/**
 * @package Neomerx\Tests\JsonApi
 */
class HeaderTest extends BaseTestCase
{
    /**
     * Test parse header.
     */
    public function testParseHeaderNameAndQualityAndParameters()
    {
        $input  = ' foo/bar.baz;media=param;q=0.5;ext="ext1,ext2", type/*, */*';
        $header = Header::parse($input, 'Content-Type');

        $this->assertEquals('Content-Type', $header->getName());

        $this->assertCount(3, $header->getMediaTypes());

        $this->assertEquals('foo', $header->getMediaTypes()[0]->getType());
        $this->assertEquals('bar.baz', $header->getMediaTypes()[0]->getSubType());
        $this->assertEquals('foo/bar.baz', $header->getMediaTypes()[0]->getMediaType());

        // Yes! 'q' for content type is not a special parameter and do not split params and extensions
        $this->assertEquals(
            ['media' => 'param', 'q' => '0.5', 'ext' => 'ext1,ext2'],
            $header->getMediaTypes()[0]->getParameters()
        );

        $this->assertEquals('type', $header->getMediaTypes()[1]->getType());
        $this->assertEquals('*', $header->getMediaTypes()[1]->getSubType());
        $this->assertEquals('type/*', $header->getMediaTypes()[1]->getMediaType());
        $this->assertEquals(null, $header->getMediaTypes()[1]->getParameters());

        $this->assertEquals('*', $header->getMediaTypes()[2]->getType());
        $this->assertEquals('*', $header->getMediaTypes()[2]->getSubType());
        $this->assertEquals('*/*', $header->getMediaTypes()[2]->getMediaType());
        $this->assertEquals(null, $header->getMediaTypes()[2]->getParameters());
    }

    /**
     * Test invalid parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParams1()
    {
        new Header('name', null);
    }

    /**
     * Test invalid parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParams2()
    {
        new Header(null, []);
    }

    /**
     * Test invalid parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParams3()
    {
        Header::parse('', '');
    }
}
