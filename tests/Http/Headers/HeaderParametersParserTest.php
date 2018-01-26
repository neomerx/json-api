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

use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class HeaderParametersParserTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /** Header name */
    const HEADER_ACCEPT = HeaderParametersParserInterface::HEADER_ACCEPT;

    /** Header name */
    const HEADER_CONTENT_TYPE = HeaderParametersParserInterface::HEADER_CONTENT_TYPE;

    /**
     * @var HeaderParametersParserInterface
     */
    private $parser;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser = (new Factory())->createHeaderParametersParser();
    }

    /**
     * Test parse parameters.
     */
    public function testParseHeadersNoParams1(): void
    {
        /** @var MediaTypeInterface $contentType */
        $contentType = $this->parser->parseContentTypeHeader(self::TYPE);
        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertNull($contentType->getParameters());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::TYPE));
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertNull($accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersNoParams2(): void
    {
        /** @var MediaTypeInterface $contentType */
        $contentType = $this->parser->parseContentTypeHeader(self::TYPE);
        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertNull($contentType->getParameters());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::TYPE . ';'));
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertNull($accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsNoExtraParams(): void
    {
        $contentType = $this->parser->parseContentTypeHeader(self::TYPE . ';ext="ext1,ext2"');
        $this->assertEquals(self::TYPE, $contentType->getMediaType());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::TYPE . ';ext=ext1'));
        $this->assertEquals(self::TYPE, $accept->getMediaType());

        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertEquals(['ext' => 'ext1,ext2'], $contentType->getParameters());
        $this->assertEquals(['ext' => 'ext1'], $accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsWithExtraParams(): void
    {
        /** @var AcceptMediaTypeInterface $accept */
        $contentType = $this->parser->parseContentTypeHeader(
            self::TYPE . ' ;  boo = foo; ext="ext1,ext2";  foo = boo '
        );
        $accept      = $this->first($this->parser->parseAcceptHeader(
            self::TYPE . ' ; boo = foo; ext=ext1;  foo = boo'
        ));

        $this->assertEquals(self::TYPE, $contentType->getMediaType());
        $this->assertEquals(self::TYPE, $accept->getMediaType());
        $this->assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1,ext2', 'foo' => 'boo'],
            $contentType->getParameters()
        );
        $this->assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1', 'foo' => 'boo'],
            $accept->getParameters()
        );
    }

    /**
     * Test parse empty header.
     *
     * @expectedException InvalidArgumentException
     */
    public function testParseEmptyHeader1(): void
    {
        $this->parser->parseContentTypeHeader('');
    }

    /**
     * Test parse empty header.
     *
     * @expectedException InvalidArgumentException
     */
    public function testParseEmptyHeader2(): void
    {
        $this->first($this->parser->parseAcceptHeader(''));
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException InvalidArgumentException
     */
    public function testParseIvalidHeaders1(): void
    {
        $this->parser->parseContentTypeHeader(self::TYPE . ';foo');
    }

    /**
     * Test parse invalid headers.
     *
     * @expectedException InvalidArgumentException
     */
    public function testParseIvalidHeaders2()
    {
        $this->first($this->parser->parseAcceptHeader(self::TYPE . ';foo'));
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part1()
    {
        $input = 'type1/*;q=0.5001, type2/*;q=0.5009';

        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getQuality(),
            $types[1]->getMediaType() => $types[1]->getQuality(),
        ];

        $this->assertCount(2, array_intersect(['type1/*' => 0.5, 'type2/*' => 0.5], $params));
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part2()
    {
        $input = 'type1/*;q=0.501, type2/*;q=0.509';

        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getQuality(),
            $types[1]->getMediaType() => $types[1]->getQuality(),
        ];

        $this->assertCount(2, array_intersect(['type1/*' => 0.501, 'type2/*' => 0.509], $params));
    }

    /**
     * Test parsing multiple params.
     */
    public function testParserHeaderWithMultipleParameters()
    {
        $input = ' foo/bar.baz;media=param;q=0.5;ext="ext1,ext2", type/*';

        /** @var AcceptMediaTypeInterface[] $types */
        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getParameters(),
            $types[1]->getMediaType() => $types[1]->getParameters(),
        ];

        asort($params);

        $this->assertEquals(['type/*' => null, 'foo/bar.baz' => ['media' => 'param']], $params);
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample1()
    {
        $input = 'audio/*; q=0.2, audio/basic';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        $this->assertCount(2, $types);
        $this->assertEquals('audio/*', $types[0]->getMediaType());
        $this->assertEquals(0.2, $types[0]->getQuality());
        $this->assertEquals(0, $types[0]->getPosition());
        $this->assertEquals('audio/basic', $types[1]->getMediaType());
        $this->assertEquals(1.0, $types[1]->getQuality());
        $this->assertEquals(1, $types[1]->getPosition());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample2()
    {
        $input = 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        $this->assertCount(4, $types);
        $this->assertEquals('text/plain', $types[0]->getMediaType());
        $this->assertEquals(0.5, $types[0]->getQuality());
        $this->assertEquals('text/html', $types[1]->getMediaType());
        $this->assertEquals(1.0, $types[1]->getQuality());
        $this->assertEquals('text/x-dvi', $types[2]->getMediaType());
        $this->assertEquals(0.8, $types[2]->getQuality());
        $this->assertEquals('text/x-c', $types[3]->getMediaType());
        $this->assertEquals(1.0, $types[3]->getQuality());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample3()
    {
        $input = 'text/*, text/html, text/html;level=1, */*';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        $this->assertCount(4, $types);
        $this->assertEquals('text/*', $types[0]->getMediaType());
        $this->assertEquals(1.0, $types[0]->getQuality());
        $this->assertEquals('text/html', $types[1]->getMediaType());
        $this->assertEquals(null, $types[0]->getParameters());
        $this->assertEquals(1.0, $types[1]->getQuality());
        $this->assertEquals(null, $types[1]->getParameters());
        $this->assertEquals('text/html', $types[2]->getMediaType());
        $this->assertEquals(1.0, $types[2]->getQuality());
        $this->assertEquals(['level' => '1'], $types[2]->getParameters());
        $this->assertEquals('*/*', $types[3]->getMediaType());
        $this->assertEquals(1.0, $types[3]->getQuality());
        $this->assertEquals(null, $types[3]->getParameters());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample4()
    {
        $input = 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        $this->assertCount(5, $types);
        $this->assertEquals('text/*', $types[0]->getMediaType());
        $this->assertEquals(0.3, $types[0]->getQuality());
        $this->assertEquals(null, $types[0]->getParameters());
        $this->assertEquals('text/html', $types[1]->getMediaType());
        $this->assertEquals(0.7, $types[1]->getQuality());
        $this->assertEquals(null, $types[1]->getParameters());
        $this->assertEquals('text/html', $types[2]->getMediaType());
        $this->assertEquals(1.0, $types[2]->getQuality());
        $this->assertEquals(['level' => '1'], $types[2]->getParameters());
        $this->assertEquals('text/html', $types[3]->getMediaType());
        $this->assertEquals(0.4, $types[3]->getQuality());
        $this->assertEquals(['level' => '2'], $types[3]->getParameters());
        $this->assertEquals('*/*', $types[4]->getMediaType());
        $this->assertEquals(0.5, $types[4]->getQuality());
        $this->assertEquals(null, $types[4]->getParameters());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidHeader1()
    {
        $this->parser->parseContentTypeHeader('');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidHeader2()
    {
        $this->parser->parseContentTypeHeader('foo/bar; baz');
    }

    /**
     * @see https://github.com/neomerx/json-api/issues/193
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidHeader3()
    {
        $this->parser->parseContentTypeHeader('application/vnd.api+json;q=0.5,text/html;q=0.8;*/*;q=0.1');
    }

    /**
     * Test invalid parse parameters.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidParseParams1()
    {
        $this->first($this->parser->parseAcceptHeader('boo.bar+baz'));
    }

    /**
     * Test invalid parse parameters.
     *
     * @expectedException InvalidArgumentException
     */
    public function testInvalidParseParams2()
    {
        $this->first($this->parser->parseAcceptHeader('boo/bar+baz;param'));
    }

    /**
     * @param iterable $iterable
     *
     * @return mixed
     */
    private function first(iterable $iterable)
    {
        foreach ($iterable as $item) {
            return $item;
        }

        throw new InvalidArgumentException();
    }

    /**
     * @param iterable $iterable
     *
     * @return array
     */
    private function iterableToArray(iterable $iterable): array
    {
        $result = [];

        foreach ($iterable as $item) {
            $result[] = $item;
        }

        return $result;
    }
}
