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

use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Http\Headers\AcceptMediaType;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class AcceptHeaderTest extends BaseTestCase
{
    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompare1(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', 'bar.baz', ['media' => 'param'], 0.5),
            new AcceptMediaType(1, 'type', '*'),
            new AcceptMediaType(2, '*', '*'),
        ];

        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'type/*',
            '*/*',
            'foo/bar.baz',
        ], $types);

        $this->assertEquals('type', $types[0]->getType());
        $this->assertEquals('*', $types[0]->getSubType());
        $this->assertEquals('type/*', $types[0]->getMediaType());
        $this->assertEquals(1, $types[0]->getQuality());
        $this->assertEquals(null, $types[0]->getParameters());

        $this->assertEquals('*', $types[1]->getType());
        $this->assertEquals('*', $types[1]->getSubType());
        $this->assertEquals('*/*', $types[1]->getMediaType());
        $this->assertEquals(1, $types[1]->getQuality());
        $this->assertEquals(null, $types[1]->getParameters());

        $this->assertEquals('foo', $types[2]->getType());
        $this->assertEquals('bar.baz', $types[2]->getSubType());
        $this->assertEquals('foo/bar.baz', $types[2]->getMediaType());
        $this->assertEquals(0.5, $types[2]->getQuality());
        $this->assertEquals(['media' => 'param'], $types[2]->getParameters());
    }

    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompareByQuality1(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', 'bar', [], 0.5),
            new AcceptMediaType(1, 'boo', 'baz', [], 0.6),
        ];

        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'boo/baz',
            'foo/bar',
        ], $types);

        $this->assertEquals('boo', $types[0]->getType());
        $this->assertEquals('baz', $types[0]->getSubType());
        $this->assertEquals('boo/baz', $types[0]->getMediaType());
        $this->assertEquals(0.6, $types[0]->getQuality());
        $this->assertEquals([], $types[0]->getParameters());

        $this->assertEquals('foo', $types[1]->getType());
        $this->assertEquals('bar', $types[1]->getSubType());
        $this->assertEquals('foo/bar', $types[1]->getMediaType());
        $this->assertEquals(0.5, $types[1]->getQuality());
        $this->assertEquals([], $types[1]->getParameters());
    }

    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompareByQuality2(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', 'bar', [], 0.5001),
            new AcceptMediaType(1, 'boo', 'baz', [], 0.5009),
        ];

        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'foo/bar',
            'boo/baz',
        ], $types);
    }

    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompareBySubType(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', '*'),
            new AcceptMediaType(1, 'boo', 'baz'),
        ];

        /** @var MediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'boo/baz',
            'foo/*',
        ], $types);

        $this->assertEquals('boo', $types[0]->getType());
        $this->assertEquals('baz', $types[0]->getSubType());
        $this->assertEquals('boo/baz', $types[0]->getMediaType());
        $this->assertEquals(1.0, $types[0]->getQuality());
        $this->assertNull($types[0]->getParameters());

        $this->assertEquals('foo', $types[1]->getType());
        $this->assertEquals('*', $types[1]->getSubType());
        $this->assertEquals('foo/*', $types[1]->getMediaType());
        $this->assertEquals(1.0, $types[1]->getQuality());
        $this->assertNull($types[1]->getParameters());
    }

    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompareByParams(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', 'bar'),
            new AcceptMediaType(1, 'boo', 'baz', ['param' => 'value']),
        ];

        /** @var MediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'boo/baz',
            'foo/bar',
        ], $types);

        $this->assertEquals('boo', $types[0]->getType());
        $this->assertEquals('baz', $types[0]->getSubType());
        $this->assertEquals('boo/baz', $types[0]->getMediaType());
        $this->assertEquals(1.0, $types[0]->getQuality());
        $this->assertEquals(['param' => 'value'], $types[0]->getParameters());

        $this->assertEquals('foo', $types[1]->getType());
        $this->assertEquals('bar', $types[1]->getSubType());
        $this->assertEquals('foo/bar', $types[1]->getMediaType());
        $this->assertEquals(1.0, $types[1]->getQuality());
        $this->assertNull($types[1]->getParameters());
    }

    /**
     * Test compare.
     *
     * @return void
     */
    public function testCompareByPosition(): void
    {
        $types = [
            new AcceptMediaType(0, 'foo', 'bar', [], 0.5),
            new AcceptMediaType(1, 'boo', 'baz', [], 0.5),
        ];

        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'foo/bar',
            'boo/baz',
        ], $types);

        $this->assertEquals('foo', $types[0]->getType());
        $this->assertEquals('bar', $types[0]->getSubType());
        $this->assertEquals('foo/bar', $types[0]->getMediaType());
        $this->assertEquals(0.5, $types[0]->getQuality());
        $this->assertEquals([], $types[0]->getParameters());

        $this->assertEquals('boo', $types[1]->getType());
        $this->assertEquals('baz', $types[1]->getSubType());
        $this->assertEquals('boo/baz', $types[1]->getMediaType());
        $this->assertEquals(0.5, $types[1]->getQuality());
        $this->assertEquals([], $types[1]->getParameters());
    }

    /**
     * Test invalid parameters.
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParameters1(): void
    {
        new AcceptMediaType(-1, 'foo', 'bar');
    }

    /**
     * Test invalid parameters.
     *
     * @return void
     *
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidParameters2(): void
    {
        new AcceptMediaType(0, 'foo', 'bar', null, 1.001);
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample1()
    {
        $types = [
            new AcceptMediaType(0, 'audio', '*', null, 0.2),
            new AcceptMediaType(1, 'audio', 'basic'),
        ];

        /** @var AcceptMediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'audio/basic',
            'audio/*',
        ], $types);
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample2()
    {
        $types = [
            new AcceptMediaType(0, 'text', 'plain', null, 0.5),
            new AcceptMediaType(1, 'text', 'html'),
            new AcceptMediaType(2, 'text', 'x-dvi', null, 0.8),
            new AcceptMediaType(3, 'text', 'x-c'),
        ];

        /** @var AcceptMediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'text/html',
            'text/x-c',
            'text/x-dvi',
            'text/plain',
        ], $types);
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample3()
    {
        $types = [
            new AcceptMediaType(0, 'text', '*'),
            new AcceptMediaType(1, 'text', 'html'),
            new AcceptMediaType(2, 'text', 'html', ['level' => '1']),
            new AcceptMediaType(3, '*', '*'),
        ];

        /** @var AcceptMediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'text/html',
            'text/html',
            'text/*',
            '*/*',
        ], $types);

        $this->assertEquals(['level' => '1'], $types[0]->getParameters());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample4()
    {
        $types = [
            new AcceptMediaType(0, 'text', '*', null, 0.3),
            new AcceptMediaType(1, 'text', 'html', null, 0.7),
            new AcceptMediaType(2, 'text', 'html', ['level' => '1']),
            new AcceptMediaType(3, 'text', 'html', ['level' => '2'], 0.4),
            new AcceptMediaType(4, '*', '*', null, 0.5),
        ];

        /** @var AcceptMediaTypeInterface[] $types */
        usort($types, AcceptMediaType::getCompare());

        $this->checkSorting([
            'text/html',
            'text/html',
            '*/*',
            'text/html',
            'text/*',
        ], $types);

        $this->assertEquals(1.0, $types[0]->getQuality());
        $this->assertEquals(0.7, $types[1]->getQuality());
        $this->assertEquals(0.4, $types[3]->getQuality());
    }

    /**
     * @param string[]             $sorted
     * @param MediaTypeInterface[] $mediaTypes
     *
     * @return void
     */
    private function checkSorting(array $sorted, array $mediaTypes): void
    {
        $this->assertEquals($count = count($mediaTypes), count($sorted));

        for ($idx = 0; $idx < $count; ++$idx) {
            $this->assertEquals($mediaTypes[$idx]->getMediaType(), $sorted[$idx]);
        }
    }
}
