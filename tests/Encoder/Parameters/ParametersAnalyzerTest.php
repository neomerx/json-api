<?php namespace Neomerx\Tests\JsonApi\Encoder\Parameters;

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

use \Mockery;
use \Mockery\MockInterface;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParametersAnalyzerTest extends BaseTestCase
{
    /**
     * @var EncodingParameters
     */
    private $parameters;

    /**
     * @var MockInterface
     */
    private $container;

    /**
     * Sets up the fixture.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parameters = new EncodingParameters(
            $paths = ['some.path'],
            $fieldsets = ['type' => ['field1', 'field2']]
        );

        $this->container = Mockery::mock(ContainerInterface::class);
    }

    /**
     * Test isPathIncluded when include path are specified.
     */
    public function testIsPathIncludedForSpecifiedIncludePaths()
    {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $analyzer = (new Factory())->createParametersAnalyzer($this->parameters, $container);

        $type = 'type';

        $this->assertTrue($analyzer->isPathIncluded('some.path', $type));
        $this->assertTrue($analyzer->isPathIncluded('some', $type));
        $this->assertFalse($analyzer->isPathIncluded('some.path.plus', $type));
        $this->assertFalse($analyzer->isPathIncluded('completely.different', $type));
    }

    /**
     * Test isPathIncluded when include path are not specified.
     */
    public function testIsPathIncludedForNotSpecifiedIncludePaths()
    {
        /** @var ContainerInterface $container */
        $container = $this->container;
        $analyzer = (new Factory())->createParametersAnalyzer(new EncodingParameters(), $container);

        $type = 'type';

        $schema1 = Mockery::mock(SchemaProviderInterface::class);
        $schema2 = Mockery::mock(SchemaProviderInterface::class);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->container->shouldReceive('getSchemaByResourceType')->zeroOrMoreTimes()->with($type)->andReturn($schema1);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema1->shouldReceive('getIncludePaths')->zeroOrMoreTimes()->withNoArgs()->andReturn(['some.path']);

        $this->assertTrue($analyzer->isPathIncluded('some.path', $type));
        $this->assertTrue($analyzer->isPathIncluded('some', $type));
        $this->assertFalse($analyzer->isPathIncluded('some.path.plus', $type));
        $this->assertFalse($analyzer->isPathIncluded('completely.different', $type));

        $type = 'some-other-type';

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->container->shouldReceive('getSchemaByResourceType')->zeroOrMoreTimes()->with($type)->andReturn($schema2);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $schema2->shouldReceive('getIncludePaths')->zeroOrMoreTimes()->withNoArgs()->andReturn(['other.path']);

        $this->assertFalse($analyzer->isPathIncluded('some.path', $type));
    }

    /**
     * Test get relationships to be included for input path.
     */
    public function testGetIncludeRelationships()
    {
        $type = 'some-type';

        /** @var ContainerInterface $container */
        $container = $this->container;
        $analyzer = (new Factory())->createParametersAnalyzer(new EncodingParameters([
            'some',
            'some.path',
            'some.path.deeper',
            'some.another.path',
            'some.another.even.deeper',
        ]), $container);

        $getRels = function ($path) use ($analyzer, $type) {
            return $analyzer->getIncludeRelationships($path, $type);
        };

        $this->assertEquals(['some' => 'some'], $getRels(null));
        $this->assertEquals(['some' => 'some'], $getRels(''));
        $this->assertEquals(['path' => 'path', 'another' => 'another'], $getRels('some'));
        $this->assertEquals(['deeper' => 'deeper'], $getRels('some.path'));
        $this->assertEquals(['path' => 'path', 'even' => 'even'], $getRels('some.another'));
    }
}
