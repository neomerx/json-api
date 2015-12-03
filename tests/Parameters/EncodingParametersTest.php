<?php namespace Neomerx\Tests\JsonApi\Parameters;

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
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Parameters\EncodingParameters;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodingParametersTest extends BaseTestCase
{
    /**
     * Test null encoding parameters.
     */
    public function testNulls()
    {
        $parameters = new EncodingParameters(null, null);

        $this->assertNull($parameters->getFieldSets());
        $this->assertNull($parameters->getIncludePaths());
        $this->assertNull($parameters->getFieldSet('whatever'));
    }

    /**
     * Test not null encoding parameters.
     */
    public function testNotNulls()
    {
        $type = 'type';
        $parameters = new EncodingParameters(
            $paths = [$type => ['some.path']],
            $fieldsets = [$type => ['field1', 'field2']]
        );

        $this->assertEquals($fieldsets, $parameters->getFieldSets());
        $this->assertEquals($paths, $parameters->getIncludePaths());
        $this->assertEquals(null, $parameters->getFieldSet('typeNotInSet'));
        $this->assertEquals($fieldsets[$type], $parameters->getFieldSet($type));
    }
}
