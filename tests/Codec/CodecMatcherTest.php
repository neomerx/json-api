<?php namespace Neomerx\Tests\JsonApi\Codec;

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
use \Neomerx\JsonApi\Codec\CodecMatcher;
use \Neomerx\JsonApi\Parameters\Headers\Header;
use \Neomerx\JsonApi\Parameters\Headers\MediaType;
use \Neomerx\JsonApi\Parameters\Headers\AcceptHeader;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CodecMatcherTest extends BaseTestCase
{
    /**
     * @var CodecMatcherInterface
     */
    private $codecMatcher;

    /**
     * @var MediaTypeInterface
     */
    private $typeNoExt;

    /**
     * @var MediaTypeInterface
     */
    private $typeExt1;

    /**
     * @var MediaTypeInterface
     */
    private $unregType;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->codecMatcher = new CodecMatcher();

        $fakeCodec = function ($name) {
            return function () use ($name) {
                return $name;
            };
        };

        $this->typeNoExt = new MediaType('type1', 'subtype1');
        $this->typeExt1  = new MediaType('type1', 'subtype1', [MediaTypeInterface::PARAM_EXT => 'ext1']);
        $this->unregType = new MediaType('type1', 'subtype1', [MediaTypeInterface::PARAM_EXT => 'ext2']);

        $this->codecMatcher->registerEncoder($this->typeNoExt, $fakeCodec('enc-type1-no-ext'));
        $this->codecMatcher->registerDecoder($this->typeNoExt, $fakeCodec('dec-type1-no-ext'));
        $this->codecMatcher->registerEncoder($this->typeExt1, $fakeCodec('enc-type1-ext1'));
        $this->codecMatcher->registerDecoder($this->typeExt1, $fakeCodec('dec-type1-ext1'));
    }

    /**
     * Test matching.
     */
    public function testMatchNoParams()
    {
        $acceptHeader = AcceptHeader::parse(
            'type1/subtype1;q=1.0, type1/subtype1;ext=ext1;q=0.8, */*;q=0.1'
        );
        $contentTypeHeader = Header::parse('type1/subtype1', HeaderInterface::HEADER_CONTENT_TYPE);

        $this->codecMatcher->matchEncoder($acceptHeader);
        $this->codecMatcher->findDecoder($contentTypeHeader);

        $this->assertEquals('enc-type1-no-ext', $this->codecMatcher->getEncoder());
        $this->assertEquals('type1/subtype1', $this->codecMatcher->getEncoderHeaderMatchedType()->getMediaType());
        $this->assertNull($this->codecMatcher->getEncoderHeaderMatchedType()->getParameters());
        $this->assertSame($this->typeNoExt, $this->codecMatcher->getEncoderRegisteredMatchedType());

        $this->assertEquals('dec-type1-no-ext', $this->codecMatcher->getDecoder());
        $this->assertEquals('type1/subtype1', $this->codecMatcher->getDecoderHeaderMatchedType()->getMediaType());
        $this->assertNull($this->codecMatcher->getDecoderHeaderMatchedType()->getParameters());
        $this->assertSame($this->typeNoExt, $this->codecMatcher->getDecoderRegisteredMatchedType());
    }

    /**
     * Test matching.
     */
    public function testMatchWithParams()
    {
        $acceptHeader = AcceptHeader::parse(
            'type1/subtype1;q=0.8, type1/subtype1;ext=ext1;q=1.0, */*;q=0.1'
        );
        $contentTypeHeader = Header::parse('type1/subtype1;ext="ext1"', HeaderInterface::HEADER_CONTENT_TYPE);

        $this->codecMatcher->matchEncoder($acceptHeader);
        $this->codecMatcher->findDecoder($contentTypeHeader);

        $this->assertEquals('enc-type1-ext1', $this->codecMatcher->getEncoder());
        $this->assertEquals('type1/subtype1', $this->codecMatcher->getEncoderHeaderMatchedType()->getMediaType());
        $this->assertEquals(['ext' => 'ext1'], $this->codecMatcher->getEncoderHeaderMatchedType()->getParameters());
        $this->assertSame($this->typeExt1, $this->codecMatcher->getEncoderRegisteredMatchedType());

        $this->assertEquals('dec-type1-ext1', $this->codecMatcher->getDecoder());
        $this->assertEquals('type1/subtype1', $this->codecMatcher->getDecoderHeaderMatchedType()->getMediaType());
        $this->assertEquals(['ext' => 'ext1'], $this->codecMatcher->getDecoderHeaderMatchedType()->getParameters());
        $this->assertSame($this->typeExt1, $this->codecMatcher->getDecoderRegisteredMatchedType());
    }

    /**
     * Test matching.
     */
    public function testNoMatch1()
    {
        $acceptHeader = AcceptHeader::parse(
            'type1-XXX/subtype1;q=0.8, type1-XXX/subtype1;ext=ext1;q=1.0, */*;q=0.1'
        );
        $contentTypeHeader = Header::parse('type1-XXX/subtype1;ext="ext1"', HeaderInterface::HEADER_CONTENT_TYPE);

        $this->codecMatcher->matchEncoder($acceptHeader);
        $this->codecMatcher->findDecoder($contentTypeHeader);

        $this->assertEquals('enc-type1-no-ext', $this->codecMatcher->getEncoder());
        $this->assertEquals('*/*', $this->codecMatcher->getEncoderHeaderMatchedType()->getMediaType());
        $this->assertNull($this->codecMatcher->getEncoderHeaderMatchedType()->getParameters());
        $this->assertSame($this->typeNoExt, $this->codecMatcher->getEncoderRegisteredMatchedType());

        $this->assertNull($this->codecMatcher->getDecoder());
        $this->assertNull($this->codecMatcher->getDecoderHeaderMatchedType());
        $this->assertNull($this->codecMatcher->getDecoderRegisteredMatchedType());
    }

    /**
     * Test matching.
     */
    public function testNoMatch2()
    {
        $acceptHeader = AcceptHeader::parse(
            'type1-XXX/subtype1;q=0.8, type1-XXX/subtype1;ext=ext1;q=1.0'
        );
        $contentTypeHeader = Header::parse('type1-XXX/subtype1;ext="ext1"', HeaderInterface::HEADER_CONTENT_TYPE);

        $this->codecMatcher->matchEncoder($acceptHeader);
        $this->codecMatcher->findDecoder($contentTypeHeader);

        $this->assertNull($this->codecMatcher->getEncoder());
        $this->assertNull($this->codecMatcher->getEncoderHeaderMatchedType());
        $this->assertNull($this->codecMatcher->getEncoderRegisteredMatchedType());

        $this->assertNull($this->codecMatcher->getDecoder());
        $this->assertNull($this->codecMatcher->getDecoderHeaderMatchedType());
        $this->assertNull($this->codecMatcher->getDecoderRegisteredMatchedType());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch1()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(AcceptHeader::parse('type1/subtype2'));
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertEquals('type1/subtype2', $best->getMediaType());
        $this->assertEquals(null, $best->getParameters());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch2()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(
            AcceptHeader::parse('type1/subtype2;q=0.4, type1/subtype2;ext="ext1,ext3";q=0.8')
        );
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertEquals('type1/subtype2', $best->getMediaType());
        $this->assertEquals(['ext' => 'ext1,ext3'], $best->getParameters());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch3()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(AcceptHeader::parse('type1/*;ext="ext1,ext3"'));
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertEquals('type1/subtype2', $best->getMediaType());
        $this->assertEquals(['ext' => 'ext1,ext3'], $best->getParameters());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch4()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(AcceptHeader::parse('*/*;ext="ext1,ext3"'));
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertEquals('type1/subtype2', $best->getMediaType());
        $this->assertEquals(['ext' => 'ext1,ext3'], $best->getParameters());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch6()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(AcceptHeader::parse('type2/*'));
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertEquals('type2/subtype1', $best->getMediaType());
        $this->assertEquals(null, $best->getParameters());
    }

    /**
     * Test get best match.
     */
    public function testGetBestMatch7()
    {
        $matcher = $this->getTestCodecMatcher();
        $matcher->matchEncoder(AcceptHeader::parse('type2/*;ext="ext1,ext3"'));
        $best = $matcher->getEncoderRegisteredMatchedType();

        $this->assertNull($best);
    }

    /**
     * When 'q' === 0.0 it means this type is not acceptable. Test it works this way.
     */
    public function testMatchWithZeroQ()
    {
        $matcher = $this->getTestCodecMatcher();

        $header = 'type1/subtype2;ext="ext1,ext3"';

        $matcher->matchEncoder(AcceptHeader::parse($header));
        $this->assertNotNull($matcher->getEncoderRegisteredMatchedType());

        $header .= ';q=0';

        $matcher->matchEncoder(AcceptHeader::parse($header));
        $this->assertNull($matcher->getEncoderRegisteredMatchedType());
    }

    /**
     * @return CodecMatcherInterface
     */
    private function getTestCodecMatcher()
    {
        $matcher = new CodecMatcher();

        $fakeEncoderClosure = function () {
        };

        $matcher->registerEncoder(new MediaType('type1', 'subtype1'), $fakeEncoderClosure);
        $matcher->registerEncoder(new MediaType('type1', 'subtype2'), $fakeEncoderClosure);
        $matcher->registerEncoder(new MediaType('type1', 'subtype2', ['ext' => 'ext1,ext3']), $fakeEncoderClosure);
        $matcher->registerEncoder(new MediaType('type2', 'subtype1'), $fakeEncoderClosure);

        return $matcher;
    }
}
