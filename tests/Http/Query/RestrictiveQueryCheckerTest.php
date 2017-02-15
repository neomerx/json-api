<?php namespace Neomerx\Tests\JsonApi\Http\Query;

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
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiException;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Http\Query\QueryCheckerInterface;
use \Neomerx\JsonApi\Contracts\Http\Query\QueryParametersParserInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RestrictiveQueryCheckerTest extends BaseTestCase
{
    /**
     * @var QueryParametersParserInterface
     */
    private $parser;

    /**
     * @var array
     */
    private $requestParams = [
        QueryParametersParserInterface::PARAM_FIELDS  => ['type1' => 'fields1,fields2'],
        QueryParametersParserInterface::PARAM_INCLUDE => 'author,comments,comments.author',
        QueryParametersParserInterface::PARAM_SORT    => '-created,+title,name.with.dots',
        QueryParametersParserInterface::PARAM_FILTER  => ['some' => 'filter'],
        QueryParametersParserInterface::PARAM_PAGE    => ['size' => 10, 'offset' => 4],
    ];

    /**
     * @var MockInterface
     */
    private $mockRequest;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser      = (new Factory())->createQueryParametersParser();
        $this->mockRequest = Mockery::mock(ServerRequestInterface::class);
    }

    /**
     * Test checker on default settings.
     */
    public function testDefaultNotReallyRestrictiveSettings()
    {
        $checker = $this->getChecker();

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with allowed input paths.
     */
    public function testAllowedInputPaths()
    {
        $checker = $this->getChecker(
            false,
            ['author', 'comments', 'comments.author', 'and.one.more.path']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with not allowed input paths.
     */
    public function testNotAllowedInputPaths()
    {
        $checker = $this->getChecker(
            false,
            ['author', 'comments']
        );
        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
            $this->assertContains('Include', $exception->getErrors()[0]->getTitle());
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with allowed field sets.
     */
    public function testAllowedFieldSets()
    {
        $checker = $this->getChecker(
            false,
            null,
            ['type1' => ['fields1', 'fields2', 'fields3'],]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with allowed field sets.
     */
    public function testAllowedAllFieldSets()
    {
        $checker = $this->getChecker(
            false,
            null,
            ['type1' => null] // all fields are allowed for type1
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with allowed filters.
     */
    public function testNotAllowedFilters()
    {
        $checker = $this->getChecker(
            false,
            null,
            null,
            null,
            null,
            ['another' => 'filter']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
            $this->assertContains('Filter', $exception->getErrors()[0]->getTitle());
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with allowed paging params.
     */
    public function testNotAllowedPaging()
    {
        $checker = $this->getChecker(
            false,
            null,
            null,
            null,
            ['another' => 'paging-param']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
            $this->assertContains('Page', $exception->getErrors()[0]->getTitle());
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with not allowed type in field sets.
     */
    public function testNonEsistingFieldSets()
    {
        $checker = $this->getChecker(
            false,
            null,
            ['nonExistingType' => null]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with not allowed field sets.
     */
    public function testNotAllowedFieldSets()
    {
        $checker = $this->getChecker(
            false,
            null,
            ['type1' => ['fields1']] // only 1 allowed field (2 in request)
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with allowed search params.
     */
    public function testAllowedSearchParams()
    {
        $allowedSortParams = ['created', 'title', 'name.with.dots', 'and-others'];
        $checker = $this->getChecker(
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with not allowed sort params.
     */
    public function testNotAllowedSortParams()
    {
        $allowedSortParams = ['created', 'name']; // in input will be 'title' which is not on the list
        $checker = $this->getChecker(
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest($this->requestParams)
        );

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
            $this->assertContains('Sort', $exception->getErrors()[0]->getTitle());
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test checker with allowed unrecognized parameters.
     */
    public function testAllowedUnrecognizedParameters()
    {
        $checker = $this->getChecker(
            true
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                array_merge($this->requestParams, ['some' => ['other', 'parameters']])
            )
        );

        $checker->checkQuery($parameters);
    }

    /**
     * Test checker with not allowed unrecognized parameters.
     */
    public function testNotAllowedUnrecognizedParameters()
    {
        $checker = $this->getChecker(
            false
        );

        $parameters = $this->parser->parse($this->prepareRequest(
            array_merge($this->requestParams, ['some' => ['other', 'parameters']])
        ));

        $exception = null;
        try {
            $checker->checkQuery($parameters);
        } catch (JsonApiException $exception) {
            $this->assertEquals([ErrorInterface::SOURCE_PARAMETER => 'some'], $exception->getErrors()[0]->getSource());
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * @param array  $input
     *
     * @return ServerRequestInterface
     */
    private function prepareRequest(array $input)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getQueryParams')->withNoArgs()->once()->andReturn($input);

        /** @var ServerRequestInterface $request */
        $request = $this->mockRequest;

        return $request;
    }

    /**
     * @param bool|false $allowUnrecognized
     * @param array|null $includePaths
     * @param array|null $fieldSetTypes
     * @param array|null $sortParameters
     * @param array|null $pagingParameters
     * @param array|null $filteringParameters
     *
     * @return QueryCheckerInterface
     */
    private function getChecker(
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        return (new Factory())->createQueryChecker(
            $allowUnrecognized,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );
    }
}
