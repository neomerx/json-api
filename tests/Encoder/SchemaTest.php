<?php namespace Neomerx\Tests\JsonApi\Encoder;

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
use \Neomerx\JsonApi\Schema\SchemaFactory;
use \Neomerx\Tests\JsonApi\Data\DummySchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class SchemaTest extends BaseTestCase
{
    /**
     * @var DummySchema
     */
    private $schema;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $schemaFactory = new SchemaFactory();
        $this->schema  = new DummySchema($schemaFactory, $schemaFactory->createContainer());
    }

    public function testGetLinks()
    {
        $this->assertEmpty($this->schema->getRelationships(null));
    }
}
