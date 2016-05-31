<?php namespace Neomerx\Tests\JsonApi\I18n;

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

use \Mockery;
use \Mockery\Mock;
use \Neomerx\JsonApi\I18n\Translator;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\I18n\TranslatorInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class TranslatorTest extends BaseTestCase
{
    /**
     * Test translator.
     */
    public function testDefaultTranslator()
    {
        $msg = Translator::getTranslator()->translate('Hello %s', ['world']);
        $this->assertEquals('Hello world', $msg);
    }

    /**
     * Test translator.
     */
    public function testCustomTranslator()
    {
        $inFormat = 'Hello %s';
        $inArgs   = ['world'];
        $outMsg   = 'こんにちは世界'; // Japanese? Ez ;-)

        /** @var Mock $translator */
        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('translate')->once()->withArgs([$inFormat, $inArgs])->andReturn($outMsg);

        /** @var TranslatorInterface $translator */

        $oldTranslator = Translator::getTranslator();
        Translator::setTranslator($translator);

        $msg = Translator::getTranslator()->translate($inFormat, $inArgs);
        $this->assertEquals($outMsg, $msg);

        // It's a static field inside. If original translator is not set back it will ruin other tests.
        Translator::setTranslator($oldTranslator);
    }
}
