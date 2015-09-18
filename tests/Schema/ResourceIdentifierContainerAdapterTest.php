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
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Schema\ResourceIdentifierContainerAdapter;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ResourceIdentifierContainerAdapterTest extends BaseTestCase
{
    public function testAdapter()
    {
        $factory   = Mockery::mock(FactoryInterface::class);
        $container = Mockery::mock(ContainerInterface::class);
        $schema    = Mockery::mock(SchemaProviderInterface::class);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $container->shouldReceive('getSchema')->once()->withAnyArgs()->andReturn($schema);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $container->shouldReceive('getSchemaByType')->once()->withAnyArgs()->andReturn($schema);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $container->shouldReceive('getSchemaByResourceType')->once()->withAnyArgs()->andReturn($schema);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $factory->shouldReceive('createResourceIdentifierSchemaAdapter')->times(3)->withAnyArgs()->andReturnUndefined();

        /** @var FactoryInterface $factory */
        /** @var ContainerInterface $container */

        $adapter = new ResourceIdentifierContainerAdapter($factory, $container);

        $resource = (object)['whatever'];
        $adapter->getSchema($resource);
        $adapter->getSchemaByType($resource);
        $adapter->getSchemaByResourceType('does not matter');
    }
}
