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
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\PaginationLinksInterface;

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
            $selfCtrlData = 'some-self-controller-data',
            $isShowSelf = false,
            $isShowMeta = false,
            $isShowSelfInIncluded = true,
            $isShowLinksInIncluded = true,
            $isShowMetaInIncluded = true
        ));

        $this->assertEquals($isInArray, $resource->isInArray());
        $this->assertEquals($type, $resource->getType());
        $this->assertEquals($idx, $resource->getId());
        $this->assertEquals($attributes, $resource->getAttributes());
        $this->assertEquals($meta, $resource->getMeta());
        $this->assertEquals($selfUrl, $resource->getSelfUrl());
        $this->assertEquals($selfCtrlData, $resource->getSelfControllerData());
        $this->assertEquals($isShowSelf, $resource->isShowSelf());
        $this->assertEquals($isShowMeta, $resource->isShowMeta());
        $this->assertEquals($isShowSelfInIncluded, $resource->isShowSelfInIncluded());
        $this->assertEquals($isShowLinksInIncluded, $resource->isShowLinksInIncluded());
        $this->assertEquals($isShowMetaInIncluded, $resource->isShowMetaInIncluded());
    }

    /**
     * Test create link object.
     */
    public function testCreateLinkObject()
    {
        $this->assertNotNull($link = $this->factory->createLinkObject(
            $name = 'link-name',
            $data = new stdClass(),
            $selfSubUrl = 'selfSubUrl',
            $relatedSubUrl = 'relatedSubUrl',
            $isShowAsRef = false,
            $isShowSelf = true,
            $isShowRelated = true,
            $isShowLinkage = true,
            $isShowMeta = true,
            $isShowPagination = true,
            $isIncluded = true,
            $selfControllerData = 'some-self-controller-data',
            $relatedControllerData = 'some-related-controller-data',
            $pagination = Mockery::mock(PaginationLinksInterface::class)
        ));

        $this->assertEquals($name, $link->getName());
        $this->assertEquals($data, $link->getLinkedData());
        $this->assertEquals($selfSubUrl, $link->getSelfSubUrl());
        $this->assertEquals($relatedSubUrl, $link->getRelatedSubUrl());
        $this->assertEquals($isShowAsRef, $link->isShowAsReference());
        $this->assertEquals($isShowSelf, $link->isShowSelf());
        $this->assertEquals($isShowRelated, $link->isShowRelated());
        $this->assertEquals($isShowLinkage, $link->isShowLinkage());
        $this->assertEquals($isShowMeta, $link->isShowMeta());
        $this->assertEquals($isShowPagination, $link->isShowPagination());
        $this->assertEquals($isIncluded, $link->isShouldBeIncluded());
        $this->assertEquals($selfControllerData, $link->getSelfControllerData());
        $this->assertEquals($relatedControllerData, $link->getRelatedControllerData());
        $this->assertSame($pagination, $link->getPagination());
    }

    /**
     * Test create pagination links.
     */
    public function testCreatePaginationLinks()
    {
        $this->assertNotNull($link = $this->factory->createPaginationLinks(
            $firstUrl = 'first',
            $lastUrl  = 'last',
            $prevUrl  = 'prev',
            $nextUrl  = 'next'
        ));

        $this->assertEquals($firstUrl, $link->getFirstUrl());
        $this->assertEquals($lastUrl, $link->getLastUrl());
        $this->assertEquals($prevUrl, $link->getPrevUrl());
        $this->assertEquals($nextUrl, $link->getNextUrl());
    }
}
