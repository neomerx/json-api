<?php namespace Neomerx\Tests\JsonApi\Parameters\Headers;

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

use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Parameters\Headers\AcceptHeader;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AcceptHeaderTest extends BaseTestCase
{
    /**
     * Test parse header.
     */
    public function testParseHeaderNameAndQualityAndParameters()
    {
        $input = ' foo/bar.baz;media=param;q=0.5;ext="ext1,ext2", type/*, */*';
        $this->checkSorting([
            'type/*',
            '*/*',
            'foo/bar.baz',
        ], $header = AcceptHeader::parse($input));

        $this->assertEquals('Accept', $header->getName());

        $this->assertEquals('type', $header->getMediaTypes()[0]->getType());
        $this->assertEquals('*', $header->getMediaTypes()[0]->getSubType());
        $this->assertEquals('type/*', $header->getMediaTypes()[0]->getMediaType());
        $this->assertEquals(1, $header->getMediaTypes()[0]->getQuality());
        $this->assertEquals(null, $header->getMediaTypes()[0]->getParameters());

        $this->assertEquals('*', $header->getMediaTypes()[1]->getType());
        $this->assertEquals('*', $header->getMediaTypes()[1]->getSubType());
        $this->assertEquals('*/*', $header->getMediaTypes()[1]->getMediaType());
        $this->assertEquals(1, $header->getMediaTypes()[1]->getQuality());
        $this->assertEquals(null, $header->getMediaTypes()[1]->getParameters());

        $this->assertEquals('foo', $header->getMediaTypes()[2]->getType());
        $this->assertEquals('bar.baz', $header->getMediaTypes()[2]->getSubType());
        $this->assertEquals('foo/bar.baz', $header->getMediaTypes()[2]->getMediaType());
        $this->assertEquals(0.5, $header->getMediaTypes()[2]->getQuality());
        $this->assertEquals(['media' => 'param'], $header->getMediaTypes()[2]->getParameters());
        $this->assertEquals(['ext' => 'ext1,ext2'], $header->getMediaTypes()[2]->getExtensions());
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part1()
    {
        $input = 'type1/*;q=0.5001, type2/*;q=0.5009';

        $this->checkSorting([
            'type1/*',
            'type2/*',
        ], $header = AcceptHeader::parse($input));

        $params = [
            $header->getMediaTypes()[0]->getMediaType() => $header->getMediaTypes()[0]->getQuality(),
            $header->getMediaTypes()[1]->getMediaType() => $header->getMediaTypes()[1]->getQuality(),
        ];

        $this->assertCount(2, array_intersect(['type1/*' => 0.5, 'type2/*' => 0.5], $params));
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part2()
    {
        $input = 'type1/*;q=0.501, type2/*;q=0.509';

        $this->checkSorting([
            'type2/*',
            'type1/*',
        ], $header = AcceptHeader::parse($input));

        $params = [
            $header->getMediaTypes()[0]->getMediaType() => $header->getMediaTypes()[0]->getQuality(),
            $header->getMediaTypes()[1]->getMediaType() => $header->getMediaTypes()[1]->getQuality(),
        ];

        $this->assertCount(2, array_intersect(['type1/*' => 0.501, 'type2/*' => 0.509], $params));
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample1()
    {
        $input = 'audio/*; q=0.2, audio/basic';
        $this->checkSorting([
            'audio/basic',
            'audio/*',
        ], $header = AcceptHeader::parse($input));
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample2()
    {
        $input  = 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c';

        $this->checkSorting([
            'text/html',
            'text/x-c',
            'text/x-dvi',
            'text/plain',
        ], $header = AcceptHeader::parse($input));

        $this->assertEquals('text/x-dvi', $header->getMediaTypes()[2]->getMediaType());
        $this->assertEquals('text/plain', $header->getMediaTypes()[3]->getMediaType());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample3()
    {
        $input = 'text/*, text/html, text/html;level=1, */*';
        $this->checkSorting([
            'text/html',
            'text/html',
            'text/*',
            '*/*',
        ], $header = AcceptHeader::parse($input));

        $this->assertEquals(['level' => '1'], $header->getMediaTypes()[0]->getParameters());
        $this->assertEquals(null, $header->getMediaTypes()[1]->getParameters());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample4()
    {
        $input = 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5';
        $header = AcceptHeader::parse($input);
        $this->checkSorting([
            'text/html',
            'text/html',
            '*/*',
            'text/html',
            'text/*',
        ], $header);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHeader1()
    {
        AcceptHeader::parse(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHeader2()
    {
        AcceptHeader::parse('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidHeader3()
    {
        AcceptHeader::parse('foo/bar; baz');
    }

    /**
     * Test invalid constructor parameters.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstructorParams()
    {
        new AcceptHeader(null);
    }

    /**
     * @param string[]        $mediaTypes
     * @param HeaderInterface $header
     *
     * @return void
     */
    private function checkSorting($mediaTypes, HeaderInterface $header)
    {
        $this->assertEquals($count = count($mediaTypes), count($sorted = $header->getMediaTypes()));

        for ($idx = 0; $idx < $count; ++$idx) {
            /** @var MediaTypeInterface $mediaType */
            $mediaType = $sorted[$idx];
            $this->assertEquals($mediaTypes[$idx], $mediaType->getMediaType());
        }
    }
}
