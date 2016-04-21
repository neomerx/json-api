<?php namespace Neomerx\Tests\JsonApi\Http\Query;

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
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Http\Headers\MediaType;
use \Neomerx\JsonApi\Http\Query\RestrictiveQueryChecker;
use \Neomerx\JsonApi\Contracts\Http\HttpFactoryInterface;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Http\Headers\RestrictiveHeadersChecker;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class FactoryTest extends BaseTestCase
{
    /**
     * @var HttpFactoryInterface
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
     * Test create media type.
     */
    public function testCreateMediaType()
    {
        $this->assertNotNull($type = $this->factory->createMediaType(
            $mediaType = 'media',
            $mediaSubType = 'type.abc',
            $parameters = ['someKey' => 'ext1,ext2']
        ));

        $this->assertEquals("$mediaType/$mediaSubType", $type->getMediaType());
        $this->assertEquals($parameters, $type->getParameters());
    }

    /**
     * Test create parameters.
     */
    public function testCreateParameters()
    {
        /** @var SortParameterInterface $sortParam */
        $sortParam = Mockery::mock(SortParameterInterface::class);
        $this->assertNotNull($parameters = $this->factory->createQueryParameters(
            $includePaths = ['some-type' => ['p1', 'p2']],
            $fieldSets = ['s1' => ['value11', 'value12']],
            $sortParameters = [$sortParam],
            $pagingParameters = ['first' => 5, 'page' => 30],
            $filteringParameters = ['some' => 'filter']
        ));

        $this->assertEquals($includePaths, $parameters->getIncludePaths());
        $this->assertEquals($fieldSets, $parameters->getFieldSets());
        $this->assertEquals($sortParameters, $parameters->getSortParameters());
        $this->assertEquals($pagingParameters, $parameters->getPaginationParameters());
        $this->assertEquals($filteringParameters, $parameters->getFilteringParameters());
    }

    /**
     * Test create encoding parameters.
     */
    public function testCreateEncodingParameters()
    {
        $this->assertNotNull($parameters = $this->factory->createQueryParameters(
            $includePaths = ['some-type' => ['p1', 'p2']],
            $fieldSets    = ['s1' => ['value11', 'value12']]
        ));

        $this->assertEquals($includePaths, $parameters->getIncludePaths());
        $this->assertEquals($fieldSets, $parameters->getFieldSets());
    }

    /**
     * Test create parameters parser.
     */
    public function testCreateParametersParser()
    {
        $this->assertNotNull($this->factory->createQueryParametersParser());
    }

    /**
     * Test create sort parameter.
     */
    public function testCreateSortParameter()
    {
        $this->assertNotNull($parameter = $this->factory->createSortParam(
            $sortField   = 'field1',
            $isAscending = true
        ));

        $this->assertEquals($sortField, $parameter->getField());
        $this->assertEquals($isAscending, $parameter->isAscending());
        $this->assertEquals(!$isAscending, $parameter->isDescending());
    }

    /**
     * Test create supported extensions.
     */
    public function testCreateSupportedExtensions()
    {
        $this->assertNotNull($supported = $this->factory->createSupportedExtensions(
            $extensions = 'ext1,ext2'
        ));

        $this->assertEquals($extensions, $supported->getExtensions());
    }

    /**
     * Test create media type for 'Accept' header.
     */
    public function testCreateAcceptMediaType()
    {
        $mediaType = $this->factory->createAcceptMediaType(
            $position = 3,
            $type = 'type',
            $subType = 'subType',
            $parameters = ['param' => 'value'],
            $quality = 0.7,
            $extensions = ['extension' => 'value']
        );

        $this->assertNotNull($mediaType);
        $this->assertEquals($position, $mediaType->getPosition());
        $this->assertEquals($type, $mediaType->getType());
        $this->assertEquals($subType, $mediaType->getSubType());
        $this->assertEquals($parameters, $mediaType->getParameters());
        $this->assertEquals($quality, $mediaType->getQuality());
        $this->assertEquals($extensions, $mediaType->getExtensions());
    }

    public function testCreateAcceptHeader()
    {
        $header = $this->factory->createAcceptHeader([new MediaType('type', 'subType')]);
        $this->assertNotNull($header);
        $this->assertCount(1, $header->getMediaTypes());
        $this->assertEquals('type/subType', $header->getMediaTypes()[0]->getMediaType());
    }

    public function testCreateParametersChecker()
    {
        /** @var CodecMatcherInterface $matcher */
        $matcher = Mockery::mock(CodecMatcherInterface::class);

        $headersChecker = $this->factory->createHeadersChecker($matcher);
        $this->assertEquals(new RestrictiveHeadersChecker($matcher), $headersChecker);

        $allowUnrecognised   = true;
        $includePaths        = ['foo', 'bar'];
        $fieldSetTypes       = ['baz', 'bat'];
        $sortParameters      = ['foobar', 'bazbat'];
        $pagingParameters    = ['bar', 'foo'];
        $filteringParameters = ['bat', 'baz'];

        $queryChecker = $this->factory->createQueryChecker(
            $allowUnrecognised,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );

        $this->assertEquals(new RestrictiveQueryChecker(
            $allowUnrecognised,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        ), $queryChecker);
    }
}
