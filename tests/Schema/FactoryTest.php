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
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Schema\SchemaFactory;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

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
        $this->factory = new SchemaFactory();
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
        $this->assertNotNull($resource = $this->factory->createResourceObject(
            $isInArray = false,
            $type = 'people',
            $idx = '123',
            $attributes = ['firstName' => 'John', 'lastName' => 'Dow'],
            $meta = ['some' => 'author meta'],
            $selfUrl = 'peopleSelfUrl/',
            $isShowSelf = false,
            $isShowMeta = false,
            $isShowSelfInIncluded = true,
            $isShowLinksInIncluded = true,
            $isShowMetaInIncluded = true,
            $isShowMetaInRelShips = true
        ));

        $this->assertEquals($isInArray, $resource->isInArray());
        $this->assertEquals($type, $resource->getType());
        $this->assertEquals($idx, $resource->getId());
        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals($meta, $resource->getMeta());
        $this->assertEquals($selfUrl, $resource->getSelfUrl());
        $this->assertEquals($isShowSelf, $resource->isShowSelf());
        $this->assertEquals($isShowMeta, $resource->isShowMeta());
        $this->assertEquals($isShowSelfInIncluded, $resource->isShowSelfInIncluded());
        $this->assertEquals($isShowLinksInIncluded, $resource->isShowRelationshipsInIncluded());
        $this->assertEquals($isShowMetaInIncluded, $resource->isShowMetaInIncluded());
        $this->assertEquals($isShowMetaInRelShips, $resource->isShowMetaInRelationships());
    }

    /**
     * Test create link object.
     */
    public function testCreateLinkObject()
    {
        $this->assertNotNull($link = $this->factory->createRelationshipObject(
            $name = 'link-name',
            $data = new stdClass(),
            $selfSubUrl = $this->factory->createLink('selfSubUrl'),
            $relatedSubUrl = $this->factory->createLink('relatedSubUrl'),
            $isShowAsRef = false,
            $isShowSelf = true,
            $isShowRelated = true,
            $isShowData = true,
            $isShowMeta = true,
            $isShowPagination = true,
            $pagination = [Mockery::mock(LinkInterface::class)]
        ));

        $this->assertEquals($name, $link->getName());
        $this->assertEquals($data, $link->getData());
        $this->assertEquals($selfSubUrl, $link->getSelfLink());
        $this->assertEquals($relatedSubUrl, $link->getRelatedLink());
        $this->assertEquals($isShowAsRef, $link->isShowAsReference());
        $this->assertEquals($isShowSelf, $link->isShowSelf());
        $this->assertEquals($isShowRelated, $link->isShowRelated());
        $this->assertEquals($isShowData, $link->isShowData());
        $this->assertEquals($isShowMeta, $link->isShowMeta());
        $this->assertEquals($isShowPagination, $link->isShowPagination());
        $this->assertSame($pagination, $link->getPagination());
    }
}
