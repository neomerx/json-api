<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Http\Headers;

/**
 * Copyright 2015-2020 info@neomerx.com
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
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\JsonApi\Http\Headers\HeaderParametersParser;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class HeaderParametersParserTest extends BaseTestCase
{
    /** JSON API type */
    const MEDIA_TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

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
    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = new HeaderParametersParser(new Factory());
    }

    /**
     * Test parse parameters.
     */
    public function testParseHeadersNoParams1(): void
    {
        /** @var MediaTypeInterface $contentType */
        $contentType = $this->parser->parseContentTypeHeader(self::MEDIA_TYPE);
        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());
        self::assertNull($contentType->getParameters());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::MEDIA_TYPE));
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());
        self::assertNull($accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersNoParams2(): void
    {
        /** @var MediaTypeInterface $contentType */
        $contentType = $this->parser->parseContentTypeHeader(self::MEDIA_TYPE);
        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());
        self::assertNull($contentType->getParameters());

        /** @var MediaTypeInterface $contentType */
        $contentType = $this->parser->parseContentTypeHeader(self::MEDIA_TYPE . ';');
        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());
        self::assertNull($contentType->getParameters());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::MEDIA_TYPE . ';'));
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());
        self::assertNull($accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsNoExtraParams(): void
    {
        $contentType = $this->parser->parseContentTypeHeader(self::MEDIA_TYPE . ';ext="ext1,ext2"');
        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());

        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first($this->parser->parseAcceptHeader(self::MEDIA_TYPE . ';ext=ext1'));
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());

        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());
        self::assertEquals(['ext' => 'ext1,ext2'], $contentType->getParameters());
        self::assertEquals(['ext' => 'ext1'], $accept->getParameters());
    }

    /**
     * Test parse headers.
     */
    public function testParseHeadersWithParamsWithExtraParams(): void
    {
        /** @var AcceptMediaTypeInterface $accept */
        $contentType = $this->parser->parseContentTypeHeader(
            self::MEDIA_TYPE . ' ;  boo = foo; ext="ext1,ext2";  foo = boo '
        );
        $accept      = $this->first(
            $this->parser->parseAcceptHeader(
                self::MEDIA_TYPE . ' ; boo = foo; ext=ext1;  foo = boo'
            )
        );

        self::assertEquals(self::MEDIA_TYPE, $contentType->getMediaType());
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());
        self::assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1,ext2', 'foo' => 'boo'],
            $contentType->getParameters()
        );
        self::assertEquals(
            ['boo' => 'foo', 'ext' => 'ext1', 'foo' => 'boo'],
            $accept->getParameters()
        );
    }

    /**
     * Test parse empty header.
     */
    public function testParseEmptyHeader1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseContentTypeHeader('');
    }

    /**
     * Test parse empty header.
     */
    public function testParseEmptyHeader2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->first($this->parser->parseAcceptHeader(''));
    }

    /**
     * Test parse invalid headers.
     */
    public function testParseInvalidHeaders1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseContentTypeHeader(self::MEDIA_TYPE . ';foo');
    }

    /**
     * Test parse invalid headers.
     */
    public function testParseInvalidHeaders2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->first($this->parser->parseAcceptHeader(self::MEDIA_TYPE . ';foo'));
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part1(): void
    {
        $input = 'type1/*;q=0.5001, type2/*;q=0.5009';

        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getQuality(),
            $types[1]->getMediaType() => $types[1]->getQuality(),
        ];

        self::assertCount(2, array_intersect(['type1/*' => 0.5, 'type2/*' => 0.5], $params));
    }

    /**
     * Test rfc2616 #3.9 (3 meaningful digits for quality)
     */
    public function testParserHeaderRfc2616P3p9Part2(): void
    {
        $input = 'type1/*;q=0.501, type2/*;q=0.509';

        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getQuality(),
            $types[1]->getMediaType() => $types[1]->getQuality(),
        ];

        self::assertCount(2, array_intersect(['type1/*' => 0.501, 'type2/*' => 0.509], $params));
    }

    /**
     * Test parsing multiple params.
     */
    public function testParserHeaderWithMultipleParameters(): void
    {
        $input = ' foo/bar.baz;media=param;q=0.5;ext="ext1,ext2", type/*';

        /** @var AcceptMediaTypeInterface[] $types */
        $types  = $this->iterableToArray($this->parser->parseAcceptHeader($input));
        $params = [
            $types[0]->getMediaType() => $types[0]->getParameters(),
            $types[1]->getMediaType() => $types[1]->getParameters(),
        ];

        asort($params);

        self::assertEquals(['type/*' => null, 'foo/bar.baz' => ['media' => 'param']], $params);
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample1(): void
    {
        $input = 'audio/*; q=0.2, audio/basic';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        self::assertEquals(2, count($types));
        self::assertEquals('audio/*', $types[0]->getMediaType());
        self::assertEquals(0.2, $types[0]->getQuality());
        self::assertEquals(0, $types[0]->getPosition());
        self::assertEquals('audio/basic', $types[1]->getMediaType());
        self::assertEquals(1.0, $types[1]->getQuality());
        self::assertEquals(1, $types[1]->getPosition());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample2(): void
    {
        $input = 'text/plain; q=0.5, text/html, text/x-dvi; q=0.8, text/x-c';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        self::assertEquals(4, count($types));
        self::assertEquals('text/plain', $types[0]->getMediaType());
        self::assertEquals(0.5, $types[0]->getQuality());
        self::assertEquals('text/html', $types[1]->getMediaType());
        self::assertEquals(1.0, $types[1]->getQuality());
        self::assertEquals('text/x-dvi', $types[2]->getMediaType());
        self::assertEquals(0.8, $types[2]->getQuality());
        self::assertEquals('text/x-c', $types[3]->getMediaType());
        self::assertEquals(1.0, $types[3]->getQuality());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample3(): void
    {
        $input = 'text/*, text/html, text/html;level=1, */*';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        self::assertEquals(4, count($types));
        self::assertEquals('text/*', $types[0]->getMediaType());
        self::assertEquals(1.0, $types[0]->getQuality());
        self::assertEquals('text/html', $types[1]->getMediaType());
        self::assertEquals(null, $types[0]->getParameters());
        self::assertEquals(1.0, $types[1]->getQuality());
        self::assertEquals(null, $types[1]->getParameters());
        self::assertEquals('text/html', $types[2]->getMediaType());
        self::assertEquals(1.0, $types[2]->getQuality());
        self::assertEquals(['level' => '1'], $types[2]->getParameters());
        self::assertEquals('*/*', $types[3]->getMediaType());
        self::assertEquals(1.0, $types[3]->getQuality());
        self::assertEquals(null, $types[3]->getParameters());
    }

    /**
     * Test sample from RFC.
     */
    public function testParseHeaderRfcSample4(): void
    {
        $input = 'text/*;q=0.3, text/html;q=0.7, text/html;level=1, text/html;level=2;q=0.4, */*;q=0.5';

        /** @var AcceptMediaTypeInterface[] $types */
        $types = $this->iterableToArray($this->parser->parseAcceptHeader($input));

        self::assertEquals(5, count($types));
        self::assertEquals('text/*', $types[0]->getMediaType());
        self::assertEquals(0.3, $types[0]->getQuality());
        self::assertEquals(null, $types[0]->getParameters());
        self::assertEquals('text/html', $types[1]->getMediaType());
        self::assertEquals(0.7, $types[1]->getQuality());
        self::assertEquals(null, $types[1]->getParameters());
        self::assertEquals('text/html', $types[2]->getMediaType());
        self::assertEquals(1.0, $types[2]->getQuality());
        self::assertEquals(['level' => '1'], $types[2]->getParameters());
        self::assertEquals('text/html', $types[3]->getMediaType());
        self::assertEquals(0.4, $types[3]->getQuality());
        self::assertEquals(['level' => '2'], $types[3]->getParameters());
        self::assertEquals('*/*', $types[4]->getMediaType());
        self::assertEquals(0.5, $types[4]->getQuality());
        self::assertEquals(null, $types[4]->getParameters());
    }

    /**
     * Test invalid header.
     */
    public function testInvalidHeader1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseContentTypeHeader('');
    }

    /**
     * Test invalid header.
     */
    public function testInvalidHeader2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseContentTypeHeader('foo/bar; baz');
    }

    /**
     * @see https://github.com/neomerx/json-api/issues/193
     */
    public function testInvalidHeader3(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->parser->parseContentTypeHeader('application/vnd.api+json;q=0.5,text/html;q=0.8;*/*;q=0.1');
    }

    /**
     * Test invalid parse parameters.
     */
    public function testInvalidParseParams1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->first($this->parser->parseAcceptHeader('boo.bar+baz'));
    }

    /**
     * Test invalid parse parameters.
     */
    public function testInvalidParseParams2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->first($this->parser->parseAcceptHeader('boo/bar+baz;param'));
    }

    /**
     * Test parse parameters.
     */
    public function testParseAcceptHeaderWithJsonApiProfile(): void
    {
        /** @var AcceptMediaTypeInterface $accept */
        $accept = $this->first(
            $this->parser->parseAcceptHeader(
                'application/vnd.api+json;profile="http://example.com/last-modified",application/vnd.api+json'
            )
        );
        self::assertEquals(self::MEDIA_TYPE, $accept->getMediaType());
        self::assertEquals(['profile' => 'http://example.com/last-modified'], $accept->getParameters());
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
