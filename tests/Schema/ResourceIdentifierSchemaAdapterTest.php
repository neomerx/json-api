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
use Mockery\Mock;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\ResourceIdentifierSchemaAdapter;
use Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ResourceIdentifierSchemaAdapterTest extends BaseTestCase
{
    public function testAdapter()
    {
        $linkMock1 = Mockery::mock(LinkInterface::class);
        $linkMock2 = Mockery::mock(LinkInterface::class);
        $linkMock3 = Mockery::mock(LinkInterface::class);
        $linkMock4 = Mockery::mock(LinkInterface::class);

        $factory = Mockery::mock(FactoryInterface::class);
        /** @var Mock $schema */
        $schema  = Mockery::mock(SchemaInterface::class);

        $schema->shouldReceive('getSelfSubLink')->once()->withAnyArgs()->andReturn($linkMock1);
        $schema->shouldReceive('getSelfSubUrl')->once()->withAnyArgs()->andReturn($linkMock2);
        $schema->shouldReceive('getLinkageMeta')->once()->withAnyArgs()->andReturn(['some' => 'meta']);
        $schema->shouldReceive('getPrimaryMeta')->once()->withAnyArgs()->andReturn(['some' => 'meta']);
        $schema->shouldReceive('getRelationshipSelfLink')->once()->withAnyArgs()->andReturn($linkMock3);
        $schema->shouldReceive('getRelationshipRelatedLink')->once()->withAnyArgs()->andReturn($linkMock4);

        /** @var FactoryInterface $factory */
        /** @var ContainerInterface $container */
        /** @var SchemaInterface $schema */

        $adapter = new ResourceIdentifierSchemaAdapter($factory, $schema);

        $resource = (object)['whatever'];
        $adapter->getSelfSubLink($resource);
        $adapter->getSelfSubUrl();
        $this->assertEmpty($adapter->getResourceLinks($resource));
        $this->assertEmpty($adapter->getIncludedResourceLinks($resource));
        $this->assertEmpty($adapter->getAttributes($resource));
        $this->assertEmpty($adapter->getRelationships($resource, true, []));
        $this->assertEmpty($adapter->getIncludePaths());
        $this->assertNotEmpty($adapter->getPrimaryMeta($resource));
        $this->assertFalse($adapter->isShowAttributesInIncluded());
        $this->assertNull($adapter->getInclusionMeta($resource));
        $this->assertNotNull($adapter->getRelationshipObjectIterator($resource, true, []));
        $this->assertNull($adapter->getRelationshipsPrimaryMeta($resource));
        $this->assertNull($adapter->getRelationshipsInclusionMeta($resource));
        $this->assertNotNull($adapter->getRelationshipSelfLink($resource, 'relationship'));
        $this->assertNotNull($adapter->getRelationshipRelatedLink($resource, 'relationship'));
        $adapter->getLinkageMeta($resource);
    }
}
