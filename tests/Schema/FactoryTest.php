<?php namespace Neomerx\Tests\JsonApi\Schema;

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
use \stdClass;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class FactoryTest extends BaseTestCase
{
    /**
     * @var SchemaFactoryInterface
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
     * Test create schema provider container.
     */
    public function testCreateContainer()
    {
        $this->assertNotNull($this->factory->createContainer());
    }

    /**
     * Test create resource object.
     */
    public function testCreateResourceObject()
    {
        $schema = Mockery::mock(SchemaProviderInterface::class);
        $schema->shouldReceive('getResourceType')->once()->andReturn('some-type');

        /** @var SchemaProviderInterface $schema */

        $this->assertNotNull($resource = $this->factory->createResourceObject(
            $schema,
            $resource = new stdClass(),
            $isInArray = false,
            $attributeKeysFilter = ['field1', 'field2']
        ));

        $this->assertEquals($isInArray, $resource->isInArray());
        $this->assertSame('some-type', $resource->getType());
    }

    /**
     * Test create relationship object.
     */
    public function testCreateRelationshipObject()
    {
        $this->assertNotNull($relationship = $this->factory->createRelationshipObject(
            $name  = 'link-name',
            $data  = new stdClass(),
            $links = [LinkInterface::SELF => $this->factory->createLink('selfSubUrl')],
            $meta  = ['some' => 'meta'],
            $isShowData = true
        ));

        $this->assertEquals($name, $relationship->getName());
        $this->assertEquals($data, $relationship->getData());
        $this->assertEquals($links, $relationship->getLinks());
        $this->assertEquals($meta, $relationship->getMeta());
        $this->assertEquals($isShowData, $relationship->isShowData());
    }
}
