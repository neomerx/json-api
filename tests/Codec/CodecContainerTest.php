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
use \Neomerx\JsonApi\Codec\CodecContainer;
use \Neomerx\JsonApi\Parameters\MediaType;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CodecContainerTest extends BaseTestCase
{
    /**
     * Test decoders.
     */
    public function testRegisterAndGet()
    {
        $containter = new CodecContainer();

        $fakeCodec = function ($name) {
            return function () use ($name) {
                return $name;
            };
        };

        $typeNoExt  = new MediaType('type1');
        $typeExt1   = new MediaType('type1', 'ext1');
        $unregType  = new MediaType('type1', 'ext2');

        $containter->registerEncoder($typeNoExt, $fakeCodec('enc-type1-no-ext'));
        $containter->registerDecoder($typeNoExt, $fakeCodec('dec-type1-no-ext'));
        $containter->registerEncoder($typeExt1, $fakeCodec('enc-type1-ext1'));
        $containter->registerDecoder($typeExt1, $fakeCodec('dec-type1-ext1'));

        $this->assertTrue($containter->isEncoderRegistered($typeNoExt));
        $this->assertTrue($containter->isDecoderRegistered($typeNoExt));
        $this->assertTrue($containter->isEncoderRegistered($typeExt1));
        $this->assertTrue($containter->isDecoderRegistered($typeExt1));
        $this->assertFalse($containter->isEncoderRegistered($unregType));
        $this->assertFalse($containter->isDecoderRegistered($unregType));

        // a bit hackish way to test. instead of encoder and decoder objects we use strings.

        $this->assertEquals('enc-type1-no-ext', $containter->getEncoder($typeNoExt));
        $this->assertEquals('dec-type1-no-ext', $containter->getDecoder($typeNoExt));
        $this->assertEquals('enc-type1-ext1', $containter->getEncoder($typeExt1));
        $this->assertEquals('dec-type1-ext1', $containter->getDecoder($typeExt1));
        $this->assertNull($containter->getEncoder($unregType));
        $this->assertNull($containter->getDecoder($unregType));
    }
}
