<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\I18n;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Neomerx\JsonApi\I18n\Messages;
use Neomerx\Tests\JsonApi\BaseTestCase;
use function Neomerx\JsonApi\I18n\format as _;

/**
 * @package Neomerx\Tests\JsonApi
 */
class MessagesTest extends BaseTestCase
{
    /**
     * Test basic function like get translation with and without custom dictionary.
     */
    public function testBasicFunctions(): void
    {
        // without custom dictionary
        self::assertEquals('foo', _('foo'));
        self::assertEquals('foo 123 text', _('foo %d %s', 123, 'text'));

        // same but with a custom dictionary
        Messages::setTranslations([
            'foo' => 'boo',
            'foo %d %s' => '%d %s boo',
        ]);
        self::assertEquals('boo', _('foo'));
        self::assertEquals('123 text boo', _('foo %d %s', 123, 'text'));
    }
}
