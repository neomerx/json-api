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

use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
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
     * @return void
     */
    public function testInvalidConstructorParams1(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MediaType('', 'subtype');
    }

    /**
     * Test invalid constructor parameters.
     *
     * @return void
     */
    public function testInvalidConstructorParams2(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new MediaType('type', '');
    }

    /**
     * Test full media type name combine.
     *
     * @return void
     */
    public function testGetMediaType(): void
    {
        $type = new MediaType('text', 'html', ['charset' => 'utf-8']);

        self::assertEquals('text/html', $type->getMediaType());
    }

    /**
     * Test compare media types (case insensitive)
     *
     * @return void
     */
    public function testCompareMediaTypes(): void
    {
        $type1 = new MediaType('text', 'html', ['charset' => 'utf-8']);
        $type2 = new MediaType('Text', 'HTML', ['Charset' => 'utf-8']);
        $type3 = new MediaType('text', 'plain', ['charset' => 'utf-8']);
        $type4 = new MediaType('text', 'html', ['otherParam' => 'utf-8']);
        $type5 = new MediaType('text', 'html', ['charset' => 'UTF-8']);
        $type6 = new MediaType('text', 'html', ['charset' => 'UTF-8', 'oneMore' => 'param']);

        self::assertTrue($type1->equalsTo($type2));
        self::assertFalse($type1->equalsTo($type3));
        self::assertFalse($type1->equalsTo($type4));
        self::assertTrue($type1->equalsTo($type5));
        self::assertFalse($type1->equalsTo($type6));
    }

    /**
     * Test compare media types (case sensitive)
     *
     * @return void
     */
    public function testCompareMediaTypes2(): void
    {
        $type1 = new MediaType('text', 'html', ['case-sensitive-value' => 'whatever']);
        $type2 = new MediaType('text', 'html', ['case-sensitive-value' => 'WHATEVER']);
        $type3 = new MediaType('text', 'html', ['CASE-SENSITIVE-VALUE' => 'whatever']);

        self::assertFalse($type1->equalsTo($type2));
        self::assertTrue($type1->equalsTo($type3));
    }

    /**
     * Test compare media types
     *
     * @return void
     */
    public function testCompareMediaTypesWithoutParameters(): void
    {
        $type1 = new MediaType('text', 'html');
        $type2 = new MediaType('Text', 'HTML');

        self::assertTrue($type1->equalsTo($type2));
    }

    /**
     * Test compare media types
     *
     * @return void
     */
    public function testMatchMediaTypes(): void
    {
        $type1 = new MediaType('text', 'html', ['charset' => 'utf-8']);
        $type2 = new MediaType('Text', 'HTML', ['Charset' => 'utf-8']);
        $type3 = new MediaType('text', '*', ['charset' => 'utf-8']);
        $type4 = new MediaType('whatever', '*', ['charset' => 'utf-8']);

        self::assertTrue($type1->matchesTo($type2));
        self::assertTrue($type1->matchesTo($type3));
        self::assertFalse($type1->matchesTo($type4));
    }

    /**
     * Test compare media types
     *
     * @return void
     */
    public function testMatchMediaTypesWithoutParameters(): void
    {
        $type1 = new MediaType('text', 'html');
        $type2 = new MediaType('Text', 'HTML');

        self::assertTrue($type1->matchesTo($type2));
    }

    /**
     * Test compare media types
     *
     * @return void
     *
     * @see https://github.com/neomerx/json-api/issues/221
     */
    public function testMatchMediaTypesWithoutParameters2(): void
    {
        // match by mask

        $type1 = new MediaType('multipart', 'form-data', ['boundary' => '*']);
        $type2 = new MediaType('multipart', 'form-data', ['boundary' => '----WebKitFormBoundaryAAA']);

        self::assertTrue($type2->matchesTo($type1));
        self::assertFalse($type2->equalsTo($type1));

        // cover some edge cases

        $type1 = new MediaType('multipart', 'form-data', ['name' => 'value1']);
        $type2 = new MediaType('multipart', 'form-data', ['name' => 'value2']);
        self::assertFalse($type2->matchesTo($type1));

        $type1 = new MediaType('multipart', 'form-data', ['name1' => 'value']);
        $type2 = new MediaType('multipart', 'form-data', ['name2' => 'value']);
        self::assertFalse($type2->matchesTo($type1));

        $type1 = new MediaType('multipart', 'form-data', ['name1' => 'value', 'name2' => 'value']);
        $type2 = new MediaType('multipart', 'form-data', ['name2' => 'value']);
        self::assertFalse($type2->matchesTo($type1));
    }
}
