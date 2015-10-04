<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue86;

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
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Parameters\RequestClosureAdapter;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Test Closure-based adapter for Request.
     */
    public function testRequestClosureAdapter()
    {
        $query   = ['sort' => '-created,title'];
        $headers = [
            HeaderInterface::HEADER_ACCEPT       => MediaTypeInterface::JSON_API_MEDIA_TYPE,
            HeaderInterface::HEADER_CONTENT_TYPE => MediaTypeInterface::JSON_API_MEDIA_TYPE,
        ];

        $request = $this->createRequest($headers, $query);
        $adapter = new RequestClosureAdapter(
            function () use ($request) {
                return $request->getBody();
            },
            function () use ($request) {
                return $request->getQueryParams();
            },
            function ($name) use ($request) {
                return $request->getHeader($name);
            }
        );

        $thrower = $this->createExceptionThrower();
        $parser  = $this->createParser();
        $result  = $parser->parse($adapter, $thrower);

        $this->assertCount(2, $result->getSortParameters());
        $createdParam = $result->getSortParameters()[0];
        $this->assertEquals('created', $createdParam->getField());
        $this->assertTrue($createdParam->isDescending());
        $titleParam   = $result->getSortParameters()[1];
        $this->assertEquals('title', $titleParam->getField());
        $this->assertTrue($titleParam->isAscending());
        $this->assertNull($adapter->getContent());
    }

    /**
     * @param array       $headers
     * @param array       $queryParams
     * @param string|null $body
     *
     * @return ServerRequestInterface
     */
    private function createRequest(array $headers, array $queryParams, $body = null)
    {
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $request->shouldReceive('getQueryParams')->zeroOrMoreTimes()->withNoArgs()->andReturn($queryParams);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $request->shouldReceive('getBody')->zeroOrMoreTimes()->withNoArgs()->andReturn($body);
        foreach ($headers as $name => $value) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $request->shouldReceive('getHeader')->zeroOrMoreTimes()->with($name)->andReturn($value);
        }

        /** @var ServerRequestInterface $request */

        return $request;
    }

    /**
     * @return ParametersParserInterface
     */
    private function createParser()
    {
        return (new Factory())->createParametersParser();
    }

    /**
     * @return ExceptionThrowerInterface
     */
    private function createExceptionThrower()
    {
        $thrower = Mockery::mock(ExceptionThrowerInterface::class);

        /** @var ExceptionThrowerInterface $thrower */

        return $thrower;
    }
}
