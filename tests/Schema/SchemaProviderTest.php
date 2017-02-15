<?php namespace Neomerx\Tests\JsonApi\Schema;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class SchemaProviderTest extends BaseTestCase
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
     * Test schema provider.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectInvalidResourceType()
    {
        EmptySchema::$type   = '';
        EmptySchema::$subUrl = '/some-sub-url/';

        new EmptySchema($this->factory);
    }

    /**
     * Test schema provider.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectInvalidSubUrl()
    {
        EmptySchema::$type   = 'someTypes';
        EmptySchema::$subUrl = '';

        new EmptySchema($this->factory);
    }

    /**
     * Test schema provider.
     */
    public function testNoTrailingSlashInGetSelfSubUrl()
    {
        EmptySchema::$type   = 'some-type';
        EmptySchema::$subUrl = null;

        $schema = new EmptySchema($this->factory);

        $this->assertEquals('/some-type', $schema->getSelfSubUrl());
    }
}
