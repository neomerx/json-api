<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Http;

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
use Mockery\MockInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Http\BaseResponses;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Neomerx\Tests\JsonApi\BaseTestCase;
use stdClass;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ResponsesTest extends BaseTestCase
{
    /**
     * @var MockInterface
     */
    private $mock;

    /**
     * @var ResponsesInterface
     */
    private $responses;

    /**
     * Set up tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock      = Mockery::mock(BaseResponses::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->responses = $this->mock;
    }

    /**
     * Test code response has no content-type header.
     */
    public function testCodeResponseHasNoContentTypeHeader(): void
    {
        $expectedHeaders = [];
        $this->willBeCalledCreateResponse(null, 123, $expectedHeaders, 'some response');
        self::assertEquals('some response', $this->responses->getCodeResponse(123));
    }

    /**
     * Test response.
     */
    public function testContentResponse1(): void
    {
        $data = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($data, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getContentResponse($data, 321));
    }

    /**
     * Test content response, with custom headers.
     */
    public function testContentResponse2(): void
    {
        $data = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($data, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals(
            'some response',
            $this->responses->getContentResponse(
                $data,
                321,
                [
                    'X-Custom' => 'Custom-Header',
                ]
            )
        );
    }

    /**
     * Test response.
     */
    public function testCreatedResponse1(): void
    {
        $resource = new stdClass();
        $location = 'http://server.tld/resource-type/123';
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($resource, 'some json api');
        $headers = [
            BaseResponses::HEADER_CONTENT_TYPE => 'some/type',
            BaseResponses::HEADER_LOCATION     => $location,
        ];
        $this->willBeCalledCreateResponse('some json api', BaseResponses::HTTP_CREATED, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getCreatedResponse($resource, $location));
    }

    /**
     * Test response, with custom headers
     */
    public function testCreatedResponse2(): void
    {
        $resource = new stdClass();
        $location = 'http://server.tld';
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($resource, 'some json api');
        $headers = [
            BaseResponses::HEADER_CONTENT_TYPE => 'some/type',
            BaseResponses::HEADER_LOCATION     => $location,
            'X-Custom'                         => 'Custom-Header',
        ];
        $this->willBeCalledCreateResponse('some json api', BaseResponses::HTTP_CREATED, $headers, 'some response');
        self::assertEquals(
            'some response',
            $this->responses->getCreatedResponse(
                $resource,
                $location,
                [
                    'X-Custom' => 'Custom-Header',
                ]
            )
        );
    }

    /**
     * Test response.
     */
    public function testMetaResponse1(): void
    {
        $meta = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForMeta($meta, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getMetaResponse($meta, 321));
    }

    /**
     * Test response, with custom headers
     */
    public function testMetaResponse2(): void
    {
        $meta = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForMeta($meta, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals(
            'some response',
            $this->responses->getMetaResponse(
                $meta,
                321,
                [
                    'X-Custom' => 'Custom-Header',
                ]
            )
        );
    }

    /**
     * Test identifiers response.
     */
    public function testIdentifiersResponse1(): void
    {
        $data = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForIdentifiers($data, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getIdentifiersResponse($data, 321));
    }

    /**
     * Test identifiers response, with custom headers.
     */
    public function testIdentifiersResponse2(): void
    {
        $data = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForIdentifiers($data, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals(
            'some response',
            $this->responses->getIdentifiersResponse(
                $data,
                321,
                [
                    'X-Custom' => 'Custom-Header',
                ]
            )
        );
    }

    /**
     * Test response.
     */
    public function testErrorResponse1(): void
    {
        $error = new Error();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForError($error, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getErrorResponse($error, 321));
    }

    /**
     * Test response.
     */
    public function testErrorResponse2(): void
    {
        $errors = [new Error()];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForErrors($errors, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getErrorResponse($errors, 321));
    }

    /**
     * Test response.
     */
    public function testErrorResponse3(): void
    {
        $errors = new ErrorCollection();
        $errors->add(new Error());
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForErrors($errors, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals('some response', $this->responses->getErrorResponse($errors, 321));
    }

    /**
     * Test response, with custom headers.
     */
    public function testErrorResponse4(): void
    {
        $error = new Error();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForError($error, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        self::assertEquals(
            'some response',
            $this->responses->getErrorResponse(
                $error,
                321,
                [
                    'X-Custom' => 'Custom-Header',
                ]
            )
        );
    }

    /**
     * @param string     $type
     * @param string     $subType
     * @param array|null $parameters
     *
     * @return void
     */
    private function willBeCalledGetMediaType(string $type, string $subType, array $parameters = null): void
    {
        $mediaType = new MediaType($type, $subType, $parameters);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('getMediaType')->once()->withNoArgs()->andReturn($mediaType);
    }

    /**
     * @param null|string $content
     * @param int         $httpCode
     * @param array       $headers
     * @param mixed       $response
     *
     * @return void
     */
    private function willBeCalledCreateResponse(?string $content, int $httpCode, array $headers, $response): void
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('createResponse')->once()
            ->withArgs([$content, $httpCode, $headers])->andReturn($response);
    }

    /**
     * @return MockInterface
     */
    private function willBeCalledGetEncoder(): MockInterface
    {
        $encoderMock = Mockery::mock(EncoderInterface::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('getEncoder')->once()->withNoArgs()->andReturn($encoderMock);

        return $encoderMock;
    }

    /**
     * @param mixed  $data
     * @param string $result
     *
     * @return void
     */
    private function willBeCalledEncoderForData($data, string $result): void
    {
        $encoderMock = $this->willBeCalledGetEncoder();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeData')
            ->once()
            ->withArgs([$data])
            ->andReturn($result);
    }

    /**
     * @param mixed  $meta
     * @param string $result
     *
     * @return void
     */
    private function willBeCalledEncoderForMeta($meta, string $result): void
    {
        $encoderMock = $this->willBeCalledGetEncoder();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeMeta')->once()->with($meta)->andReturn($result);
    }

    /**
     * @param mixed  $data
     * @param string $result
     *
     * @return void
     */
    private function willBeCalledEncoderForIdentifiers($data, string $result): void
    {
        $encoderMock = $this->willBeCalledGetEncoder();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeIdentifiers')
            ->once()
            ->withArgs([$data])
            ->andReturn($result);
    }

    /**
     * @param mixed  $error
     * @param string $result
     *
     * @return void
     */
    private function willBeCalledEncoderForError($error, string $result): void
    {
        $encoderMock = $this->willBeCalledGetEncoder();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeError')->once()->with($error)->andReturn($result);
    }

    /**
     * @param iterable $errors
     * @param string   $result
     *
     * @return void
     */
    private function willBeCalledEncoderForErrors(iterable $errors, string $result): void
    {
        $encoderMock = $this->willBeCalledGetEncoder();

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeErrors')->once()->with($errors)->andReturn($result);
    }
}
