<?php namespace Neomerx\Tests\JsonApi\Decoders;

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
use \Neomerx\JsonApi\Decoders\RawDecoder;
use \Neomerx\JsonApi\Decoders\ArrayDecoder;
use \Neomerx\JsonApi\Decoders\ObjectDecoder;

/**
 * @package Neomerx\Tests\JsonApi
 */
class DecoderTest extends BaseTestCase
{
    /**
     * Test decoders.
     */
    public function testDecoders()
    {
        $content = '{ "field": "value" }';

        $rawDecoder = new RawDecoder();
        $this->assertEquals($content, $rawDecoder->decode($content));

        $arrayDecoder = new ArrayDecoder();
        $this->assertEquals(['field' => 'value'], $arrayDecoder->decode($content));

        $objectDecoder = new ObjectDecoder();
        $this->assertEquals((object)['field' => 'value'], $objectDecoder->decode($content));
    }
}
