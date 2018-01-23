<?php namespace Neomerx\Tests\JsonApi\Exceptions;

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

use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ErrorCollectionTest extends BaseTestCase
{
    /** Path constant */
    const DATA_PATH = '/data';

    /** Path constant */
    const DATA_TYPE_PATH = '/data/type';

    /** Path constant */
    const DATA_ID_PATH = '/data/id';

    /** Path constant */
    const ATTR_PATH = '/data/attributes';

    /** Path constant */
    const RELS_PATH = '/data/relationships';

    /**
     * @var ErrorCollection
     */
    private $collection;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->collection = new ErrorCollection();
    }

    public function testBasicCollectionMethods()
    {
        $this->assertCount(0, $this->collection);
        $title1 = 'some title 1';
        $title2 = 'some title 2';
        $this->collection->addDataError($title1);
        $this->assertCount(1, $copy = $this->collection->getArrayCopy());
        $this->collection->addDataError($title2);
        $this->assertCount(1, $copy);
        $this->assertCount(2, $this->collection);
        $this->assertCount(2, $this->collection->getArrayCopy());

        $this->assertTrue(isset($this->collection[0]));
        $this->assertTrue(isset($this->collection[1]));

        $this->assertEquals($title1, $this->collection[0]->getTitle());
        $this->assertEquals($title2, $this->collection[1]->getTitle());
        $tmp = $this->collection[0];
        $this->collection[0] = $this->collection[1];
        $this->collection[1] = $tmp;
        $this->assertEquals($title1, $this->collection[1]->getTitle());
        $this->assertEquals($title2, $this->collection[0]->getTitle());

        foreach ($this->collection as $error) {
            $this->assertInstanceOf(Error::class, $error);
        }

        $serialized        = $this->collection->serialize();
        $anotherCollection = new ErrorCollection();
        $anotherCollection->unserialize($serialized);
        $this->assertEquals($this->collection, $anotherCollection);

        $this->assertCount(2, $this->collection);
        unset($this->collection[0]);
        unset($this->collection[1]);
        $this->assertCount(0, $this->collection);
    }

    /**
     * Test adding error.
     */
    public function testAddDataError()
    {
        $this->collection->addDataError('some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::DATA_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddDataTypeError()
    {
        $this->collection->addDataTypeError('some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::DATA_TYPE_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddDataIdError()
    {
        $this->collection->addDataIdError('some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::DATA_ID_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddAttributesError()
    {
        $this->collection->addAttributesError('some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::ATTR_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddDataAttributeError()
    {
        $this->collection->addDataAttributeError('name', 'some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::ATTR_PATH . '/name'
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddRelationshipsError()
    {
        $this->collection->addRelationshipsError('some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::RELS_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddRelationshipError()
    {
        $this->collection->addRelationshipError('name', 'some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::RELS_PATH . '/name'
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddRelationshipTypeError()
    {
        $this->collection->addRelationshipTypeError('name', 'some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::RELS_PATH . '/name' . self::DATA_TYPE_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddRelationshipIdError()
    {
        $this->collection->addRelationshipIdError('name', 'some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_POINTER => self::RELS_PATH . '/name' . self::DATA_ID_PATH
        ], $this->collection[0]->getSource());
    }

    /**
     * Test adding error.
     */
    public function testAddQueryParameterError()
    {
        $this->collection->addQueryParameterError('name', 'some title');
        $this->assertNotEmpty($this->collection);
        $this->assertEquals([
            Error::SOURCE_PARAMETER => 'name'
        ], $this->collection[0]->getSource());
    }
}
