<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Parser;

/**
 * Copyright 2015-2020 info@neomerx.com
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

use Mockery;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipDataInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface;
use Neomerx\JsonApi\Exceptions\LogicException;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsCollection;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsIdentifier;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsNull;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsResource;
use Neomerx\Tests\JsonApi\BaseTestCase;
use stdClass;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RelationshipDataTest extends BaseTestCase
{
    /**
     * Test relationship data.
     */
    public function testIsCollection(): void
    {
        $data = $this->createRelationshipData(RelationshipDataIsCollection::class, []);

        $this->assertTrue($data->isCollection());
        $this->assertFalse($data->isNull());
        $this->assertFalse($data->isResource());
        $this->assertFalse($data->isIdentifier());
        $this->assertMethodThrowsLogicException($data, 'getIdentifier');
        $this->assertMethodThrowsLogicException($data, 'getResource');
    }

    /**
     * Test relationship data.
     */
    public function testIsIdentifier(): void
    {
        $data = $this->createRelationshipData(
            RelationshipDataIsIdentifier::class,
            Mockery::mock(IdentifierInterface::class)
        );

        $this->assertFalse($data->isCollection());
        $this->assertFalse($data->isNull());
        $this->assertFalse($data->isResource());
        $this->assertTrue($data->isIdentifier());
        $this->assertMethodThrowsLogicException($data, 'getIdentifiers');
        $this->assertMethodThrowsLogicException($data, 'getResource');
        $this->assertMethodThrowsLogicException($data, 'getResources');
    }

    /**
     * Test relationship data.
     */
    public function testIsNull(): void
    {
        $data = $this->createRelationshipData(
            RelationshipDataIsNull::class,
            null
        );

        $this->assertFalse($data->isCollection());
        $this->assertTrue($data->isNull());
        $this->assertFalse($data->isResource());
        $this->assertFalse($data->isIdentifier());
        $this->assertMethodThrowsLogicException($data, 'getIdentifier');
        $this->assertMethodThrowsLogicException($data, 'getIdentifiers');
        $this->assertMethodThrowsLogicException($data, 'getResource');
        $this->assertMethodThrowsLogicException($data, 'getResources');
    }

    /**
     * Test relationship data.
     */
    public function testIsResource(): void
    {
        $data = $this->createRelationshipData(
            RelationshipDataIsResource::class,
            new stdClass()
        );

        $this->assertFalse($data->isCollection());
        $this->assertFalse($data->isNull());
        $this->assertTrue($data->isResource());
        $this->assertFalse($data->isIdentifier());
        $this->assertMethodThrowsLogicException($data, 'getIdentifier');
        $this->assertMethodThrowsLogicException($data, 'getIdentifiers');
        $this->assertMethodThrowsLogicException($data, 'getResources');
    }

    /**
     * @param string $className
     * @param mixed  $specificParam
     *
     * @return RelationshipDataInterface
     */
    private function createRelationshipData(string $className, $specificParam): RelationshipDataInterface
    {
        $factory   = $this->createFactory();
        $container = $factory->createSchemaContainer([]);
        $context   = $factory->createParserContext([], []);
        $position  = $factory->createPosition(
            ParserInterface::ROOT_LEVEL,
            ParserInterface::ROOT_PATH,
            null,
            null
        );

        $data = new $className($factory, $container, $context, $position, $specificParam);

        return $data;
    }

    /**
     * @param RelationshipDataInterface $data
     * @param string                    $method
     *
     * @return void
     */
    private function assertMethodThrowsLogicException(RelationshipDataInterface $data, string $method): void
    {
        $this->assertTrue(method_exists($data, $method));

        $wasThrown = false;
        try {
            call_user_func([$data, $method]);
        } catch (LogicException $exception) {
            $wasThrown = true;
        }

        $this->assertTrue($wasThrown);
    }
}
