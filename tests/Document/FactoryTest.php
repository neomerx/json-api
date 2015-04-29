<?php namespace Neomerx\Tests\JsonApi\Document;

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
use \Neomerx\JsonApi\Document\DocumentFactory;

/**
 * @package Neomerx\Tests\JsonApi
 */
class FactoryTest extends BaseTestCase
{

    /**
     * @var DocumentFactory
     */
    private $factory;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->factory  = new DocumentFactory();
    }

    /**
     * Test create document.
     */
    public function testCreateDocument()
    {
        $this->assertNotNull($this->factory->createDocument());
    }

    /**
     * Test create error.
     */
    public function testCreateError()
    {
        $this->assertNotNull($error = $this->factory->createError(
            $idx = 'some-id',
            $href = 'some-href',
            $status = 'some-status',
            $code = 'some-code',
            $title = 'some-title',
            $detail = 'some-detail',
            $links = ['link1'],
            $paths = ['paths'],
            $members = ['members']
        ));

        $this->assertEquals($idx, $error->getId());
        $this->assertEquals($href, $error->getHref());
        $this->assertEquals($status, $error->getStatus());
        $this->assertEquals($code, $error->getCode());
        $this->assertEquals($title, $error->getTitle());
        $this->assertEquals($detail, $error->getDetail());
        $this->assertEquals($links, $error->getLinks());
        $this->assertEquals($paths, $error->getPaths());
        $this->assertEquals($members, $error->getAdditionalMembers());
    }
}
