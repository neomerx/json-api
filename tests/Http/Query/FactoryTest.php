<?php namespace Neomerx\Tests\JsonApi\Http\Query;

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

use Neomerx\JsonApi\Contracts\Http\HttpFactoryInterface;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class FactoryTest extends BaseTestCase
{
    /**
     * @var HttpFactoryInterface
     */
    private $factory;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory = new Factory();
    }

    /**
     * Test create media type.
     */
    public function testCreateMediaType()
    {
        $this->assertNotNull($type = $this->factory->createMediaType(
            $mediaType = 'media',
            $mediaSubType = 'type.abc',
            $parameters = ['someKey' => 'ext1,ext2']
        ));

        $this->assertEquals("$mediaType/$mediaSubType", $type->getMediaType());
        $this->assertEquals($parameters, $type->getParameters());
    }

    /**
     * Test create parameters.
     */
    public function testCreateParameters()
    {
        $this->assertNotNull($parameters = $this->factory->createQueryParameters(
            $includePaths = ['some-type' => ['p1', 'p2']],
            $fieldSets = ['s1' => ['value11', 'value12']]
        ));

        $this->assertEquals($includePaths, $parameters->getIncludePaths());
        $this->assertEquals($fieldSets, $parameters->getFieldSets());
    }

    /**
     * Test create encoding parameters.
     */
    public function testCreateEncodingParameters()
    {
        $this->assertNotNull($parameters = $this->factory->createQueryParameters(
            $includePaths = ['some-type' => ['p1', 'p2']],
            $fieldSets = ['s1' => ['value11', 'value12']]
        ));

        $this->assertEquals($includePaths, $parameters->getIncludePaths());
        $this->assertEquals($fieldSets, $parameters->getFieldSets());
    }

    public function testCreateAcceptHeader()
    {
        $header = $this->factory->createAcceptHeader([new MediaType('type', 'subType')]);
        $this->assertNotNull($header);
        $this->assertCount(1, $header->getMediaTypes());
        $this->assertEquals('type/subType', $header->getMediaTypes()[0]->getMediaType());
    }
}
