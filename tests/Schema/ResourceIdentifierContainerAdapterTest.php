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

use Mockery;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\ResourceIdentifierContainerAdapter;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ResourceIdentifierContainerAdapterTest extends BaseTestCase
{
    public function testAdapter()
    {
        $factory   = Mockery::mock(FactoryInterface::class);
        $container = Mockery::mock(ContainerInterface::class);
        $provider  = Mockery::mock(SchemaInterface::class);

        /** @var Mockery\Mock $factory */
        /** @var Mockery\Mock $container */
        /** @var Mockery\Mock $provider */

        $container->shouldReceive('getSchema')->once()->withAnyArgs()->andReturn($provider);
        $container->shouldReceive('getSchemaByType')->once()->withAnyArgs()->andReturn($provider);
        $container->shouldReceive('getSchemaByResourceType')->once()->withAnyArgs()->andReturn($provider);
        $factory->shouldReceive('createResourceIdentifierSchemaAdapter')->times(3)->withAnyArgs()
            ->andReturn($provider);

        /** @var FactoryInterface $factory */
        /** @var ContainerInterface $container */

        $adapter = new ResourceIdentifierContainerAdapter($factory, $container);

        $resource = (object)['whatever'];
        $this->assertNotNull($adapter->getSchema($resource));
        $this->assertNotNull($adapter->getSchemaByType('does not matter 1'));
        $this->assertNotNull($adapter->getSchemaByResourceType('does not matter2'));
    }
}
