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

use \Neomerx\JsonApi\Document\Link;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class FactoryTest extends BaseTestCase
{

    /**
     * @var DocumentFactoryInterface
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
            $link = new Link('about-link'),
            $status = 'some-status',
            $code = 'some-code',
            $title = 'some-title',
            $detail = 'some-detail',
            $source = ['source' => 'info'],
            $meta = ['meta' => 'info']
        ));

        $this->assertEquals($idx, $error->getId());
        $this->assertEquals([DocumentInterface::KEYWORD_ERRORS_ABOUT => $link], $error->getLinks());
        $this->assertEquals($status, $error->getStatus());
        $this->assertEquals($code, $error->getCode());
        $this->assertEquals($title, $error->getTitle());
        $this->assertEquals($detail, $error->getDetail());
        $this->assertEquals($source, $error->getSource());
        $this->assertEquals($meta, $error->getMeta());
    }
}
