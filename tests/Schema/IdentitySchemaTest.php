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

use LogicException;
use Mockery;
use Mockery\Mock;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\IdentitySchema;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IdentitySchemaTest extends BaseTestCase
{
    /**
     * @var IdentitySchema
     */
    private $schema;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var Mock $providerMock */
        $providerMock = Mockery::mock(SchemaInterface::class);
        $providerMock->shouldReceive('getResourceType')->once()->withAnyArgs()->andReturn('fake-type');
        $providerMock->shouldReceive('getSelfSubUrl')->once()->withAnyArgs()->andReturn('/fake-sub-url');

        /** @var SchemaInterface $providerMock */

        /** @var SchemaFactoryInterface $factory */
        $factory = Mockery::mock(SchemaFactoryInterface::class);

        /** @var Mock $container */
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('getSchemaByType')->once()->withAnyArgs()->andReturn($providerMock);

        /** @var ContainerInterface $container */

        $this->schema = new IdentitySchema($factory, $container, 'fake class name', function () {
            throw new LogicException();
        });
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetAttributes()
    {
        $this->schema->getAttributes((object)[]);
    }

    /**
     * @expectedException \LogicException
     */
    public function testGetRelationships()
    {
        $this->schema->getRelationships((object)[], true, []);
    }
}
