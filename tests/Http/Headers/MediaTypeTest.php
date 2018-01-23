<?php namespace Neomerx\Tests\JsonApi\Http\Headers;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class MediaTypeTest extends BaseTestCase
{
    /**
     * Test invalid constructor parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructorParams1()
    {
        new MediaType('', 'subtype');
    }

    /**
     * Test invalid constructor parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructorParams2()
    {
        new MediaType('type', '');
    }

    /**
     * Test invalid parse parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParseParams1()
    {
        MediaType::parse(1, 'boo.bar+baz');
    }

    /**
     * Test invalid parse parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParseParams2()
    {
        MediaType::parse(1, 'boo/bar+baz;param');
    }

    /**
     * Test compare media types
     */
    public function testCompareMediaTypes()
    {
        $type1 = MediaType::parse(0, 'text/html;charset=utf-8');
        $type2 = MediaType::parse(0, 'Text/HTML; Charset="utf-8"');
        $type3 = MediaType::parse(0, 'text/plain;charset=utf-8');
        $type4 = MediaType::parse(0, 'text/html;otherParam=utf-8');
        $type5 = MediaType::parse(0, 'text/html;charset=UTF-8');
        $type6 = MediaType::parse(0, 'text/html;charset=UTF-8;oneMore=param');

        $this->assertTrue($type1->equalsTo($type2));
        $this->assertFalse($type1->equalsTo($type3));
        $this->assertFalse($type1->equalsTo($type4));
        $this->assertTrue($type1->equalsTo($type5));
        $this->assertFalse($type1->equalsTo($type6));
    }

    /**
     * Test compare media types
     */
    public function testMatchMediaTypes()
    {
        $type1 = MediaType::parse(0, 'text/html;charset=utf-8');
        $type2 = MediaType::parse(0, 'Text/HTML; Charset="utf-8"');
        $type3 = MediaType::parse(0, 'text/*;charset=utf-8');
        $type4 = MediaType::parse(0, 'whatever/*;charset=utf-8');

        $this->assertTrue($type1->matchesTo($type2));
        $this->assertTrue($type1->matchesTo($type3));
        $this->assertFalse($type1->matchesTo($type4));
    }
}
