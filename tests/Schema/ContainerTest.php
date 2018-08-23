<?php namespace Neomerx\Tests\JsonApi\Schema;

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

use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Factories\Factory;
use Neomerx\JsonApi\Schema\Container;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Author;
use Neomerx\Tests\JsonApi\Data\AuthorInterface;
use Neomerx\Tests\JsonApi\Data\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Post;
use stdClass;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ContainerTest extends BaseTestCase
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
     * Test container.
     */
    public function testGetSchemaByType()
    {
        $this->assertNotNull($container = $this->factory->createContainer([
            Author::class => AuthorSchema::class,
        ]));

        $this->assertNotNull($container->getSchemaByType(Author::class));

        $gotException = false;
        try {
            $container->getSchemaByType(Post::class);
        } catch (InvalidArgumentException $exception) {
            $gotException = true;
        }

        $this->assertTrue($gotException);
    }

    /**
     * Test container.
     */
    public function testGetSchemaByResourceType()
    {
        $this->assertNotNull($container = $this->factory->createContainer([
            Author::class => AuthorSchema::class,
        ]));

        $this->assertNotNull($container->getSchemaByResourceType('people'));

        $gotException = false;
        try {
            $container->getSchemaByResourceType('posts');
        } catch (InvalidArgumentException $exception) {
            $gotException = true;
        }

        $this->assertTrue($gotException);
    }

    /**
     * Test container.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterInvalidSchemaMapping1()
    {
        $this->factory->createContainer([
            '' => AuthorSchema::class,
        ]);
    }

    /**
     * Test container.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterInvalidSchemaMapping2()
    {
        $this->factory->createContainer([
            Author::class => '',
        ]);
    }

    /**
     * Test container.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterInvalidSchemaMapping3()
    {
        $container = new Container($this->factory, [
            Author::class => AuthorSchema::class,
        ]);

        $container->register(Author::class, AuthorSchema::class);
    }

    /**
     * Test container.
     *
     * @link https://github.com/neomerx/json-api/issues/168
     */
    public function testRegisterSchemaInstance()
    {
        $authorSchema = new AuthorSchema($this->factory);
        $container    = new Container($this->factory, [
            Author::class => $authorSchema,
        ]);

        $this->assertSame($authorSchema, $container->getSchema(new Author()));
    }

    public function testRegisterSchemaInstanceWhenInterfaceIsMapped()
    {
        $authorSchema = new AuthorSchema($this->factory);
        $container    = new Container($this->factory, [
            AuthorInterface::class => $authorSchema,
        ]);

        $this->assertSame($authorSchema, $container->getSchema(new Author()));
    }

    public function testRegisterSchemaInstanceWhenParentClassIsMapped()
    {
        $authorSchema = new AuthorSchema($this->factory);
        $container    = new Container($this->factory, [
            stdClass::class => $authorSchema,
        ]);

        $this->assertSame($authorSchema, $container->getSchema(new Author()));
    }

    /**
     * Test container.
     *
     * @link https://github.com/neomerx/json-api/issues/177
     */
    public function testRegisterCallableSchemaFactory()
    {
        $container = new Container($this->factory, [
            Author::class => [static::class, 'authorSchemaFactory'],
        ]);

        $this->assertNotNull($container->getSchema(new Author()));
    }

    /**
     * Test container.
     *
     * @link https://github.com/neomerx/json-api/issues/188
     */
    public function testForNullResourceShouldReturnNull()
    {
        $container = new Container($this->factory, [
            Author::class => [static::class, 'authorSchemaFactory'],
        ]);

        $this->assertNull($container->getSchema(null));
    }

    /**
     * @param SchemaFactoryInterface $factory
     *
     * @return AuthorSchema
     */
    public static function authorSchemaFactory(SchemaFactoryInterface $factory)
    {
        return new AuthorSchema($factory);
    }
}
