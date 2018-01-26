<?php namespace Neomerx\Tests\JsonApi\Http;

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
use Mockery\MockInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\ErrorCollection;
use Neomerx\JsonApi\Http\BaseResponses;
use Neomerx\JsonApi\Http\Headers\MediaType;
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
     * @var EncodingParametersInterface|null
     */
    private $encodingencodingParameters = null;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mock      = Mockery::mock(BaseResponses::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->responses = $this->mock;
    }

    /**
     * Test code response has no content-type header.
     */
    public function testCodeResponseHasNoContentTypeHeader()
    {
        $expectedHeaders = [];
        $this->willBeCalledCreateResponse(null, 123, $expectedHeaders, 'some response');
        $this->assertEquals('some response', $this->responses->getCodeResponse(123));
    }

    /**
     * Test response.
     */
    public function testContentResponse1()
    {
        $data  = new stdClass();
        $links = ['some' => 'links'];
        $meta  = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($data, 'some json api', $links, $meta);
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getContentResponse($data, 321, $links, $meta));
    }

    /**
     * Test content response, with custom headers.
     */
    public function testContentResponse2()
    {
        $data  = new stdClass();
        $links = ['some' => 'links'];
        $meta  = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($data, 'some json api', $links, $meta);
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getContentResponse($data, 321, $links, $meta, [
            'X-Custom' => 'Custom-Header',
        ]));
    }

    /**
     * Test response.
     */
    public function testCreatedResponse1()
    {
        $resource = new stdClass();
        $links    = ['some' => 'links'];
        $meta     = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($resource, 'some json api', $links, $meta);
        $this->willBeCreatedResourceLocationUrl($resource, 'http://server.tld', '/resource-type/123');
        $headers = [
            BaseResponses::HEADER_CONTENT_TYPE => 'some/type',
            BaseResponses::HEADER_LOCATION     => 'http://server.tld/resource-type/123',
        ];
        $this->willBeCalledCreateResponse('some json api', BaseResponses::HTTP_CREATED, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getCreatedResponse($resource, $links, $meta));
    }

    /**
     * Test response, with custom headers
     */
    public function testCreatedResponse2()
    {
        $resource = new stdClass();
        $links    = ['some' => 'links'];
        $meta     = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForData($resource, 'some json api', $links, $meta);
        $this->willBeCreatedResourceLocationUrl($resource, 'http://server.tld', '/resource-type/123');
        $headers = [
            BaseResponses::HEADER_CONTENT_TYPE => 'some/type',
            BaseResponses::HEADER_LOCATION     => 'http://server.tld/resource-type/123',
            'X-Custom'                         => 'Custom-Header',
        ];
        $this->willBeCalledCreateResponse('some json api', BaseResponses::HTTP_CREATED, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getCreatedResponse($resource, $links, $meta, [
            'X-Custom' => 'Custom-Header',
        ]));
    }

    /**
     * Test response.
     */
    public function testMetaResponse1()
    {
        $meta = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForMeta($meta, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getMetaResponse($meta, 321));
    }

    /**
     * Test response, with custom headers
     */
    public function testMetaResponse2()
    {
        $meta = new stdClass();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForMeta($meta, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getMetaResponse($meta, 321, [
            'X-Custom' => 'Custom-Header',
        ]));
    }

    /**
     * Test identifiers response.
     */
    public function testIdentifiersResponse1()
    {
        $data  = new stdClass();
        $links = ['some' => 'links'];
        $meta  = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForIdentifiers($data, 'some json api', $links, $meta);
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getIdentifiersResponse($data, 321, $links, $meta));
    }

    /**
     * Test identifiers response, with custom headers.
     */
    public function testIdentifiersResponse2()
    {
        $data  = new stdClass();
        $links = ['some' => 'links'];
        $meta  = ['some' => 'meta'];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForIdentifiers($data, 'some json api', $links, $meta);
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getIdentifiersResponse($data, 321, $links, $meta, [
            'X-Custom' => 'Custom-Header',
        ]));
    }

    /**
     * Test response.
     */
    public function testErrorResponse1()
    {
        $error = new Error();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForError($error, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getErrorResponse($error, 321));
    }

    /**
     * Test response.
     */
    public function testErrorResponse2()
    {
        $errors = [new Error()];
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForErrors($errors, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getErrorResponse($errors, 321));
    }

    /**
     * Test response.
     */
    public function testErrorResponse3()
    {
        $errors = new ErrorCollection();
        $errors->add(new Error());
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForErrors($errors, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getErrorResponse($errors, 321));
    }

    /**
     * Test response, with custom headers.
     */
    public function testErrorResponse4()
    {
        $error = new Error();
        $this->willBeCalledGetMediaType('some', 'type');
        $this->willBeCalledEncoderForError($error, 'some json api');
        $headers = [BaseResponses::HEADER_CONTENT_TYPE => 'some/type', 'X-Custom' => 'Custom-Header'];
        $this->willBeCalledCreateResponse('some json api', 321, $headers, 'some response');
        $this->assertEquals('some response', $this->responses->getErrorResponse($error, 321, [
            'X-Custom' => 'Custom-Header',
        ]));
    }

    /**
     * @param string     $type
     * @param string     $subType
     * @param array|null $parameters
     *
     * @return void
     */
    private function willBeCalledGetMediaType($type, $subType, array $parameters = null)
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
    private function willBeCalledCreateResponse($content, $httpCode, array $headers, $response)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('createResponse')->once()
            ->withArgs([$content, $httpCode, $headers])->andReturn($response);
    }

    /**
     * @param bool $withEncodingParams
     *
     * @return MockInterface
     */
    private function willBeCalledGetEncoder($withEncodingParams)
    {
        $encoderMock = Mockery::mock(EncoderInterface::class);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('getEncoder')->once()->withNoArgs()->andReturn($encoderMock);
        if ($withEncodingParams === true) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $this->mock->shouldReceive('getEncodingParameters')
                ->once()->withNoArgs()->andReturn($this->encodingencodingParameters);
        }

        return $encoderMock;
    }

    /**
     * @param mixed      $data
     * @param string     $result
     * @param array|null $links
     * @param mixed      $meta
     *
     * @return void
     */
    private function willBeCalledEncoderForData($data, $result, array $links = null, $meta = null)
    {
        $encoderMock = $this->willBeCalledGetEncoder(true);

        if ($links !== null) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $encoderMock->shouldReceive('withLinks')->once()->with($links)->andReturnSelf();
        }

        if ($meta !== null) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $encoderMock->shouldReceive('withMeta')->once()->with($meta)->andReturnSelf();
        }

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeData')
            ->once()
            ->withArgs([$data, $this->encodingencodingParameters])
            ->andReturn($result);
    }

    /**
     * @param mixed       $resource
     * @param string|null $prefix
     * @param string      $subUrl
     *
     * @return void
     */
    private function willBeCreatedResourceLocationUrl($resource, $prefix, $subUrl)
    {
        $linkMock = Mockery::mock(LinkInterface::class);

        $containerMock = Mockery::mock(ContainerInterface::class);
        $providerMock  = Mockery::mock(SchemaInterface::class);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mock->shouldReceive('getSchemaContainer')->once()->withNoArgs()->andReturn($containerMock);

        $containerMock->shouldReceive('getSchema')->once()->with($resource)->andReturn($providerMock);
        $providerMock->shouldReceive('getSelfSubLink')->once()->with($resource)->andReturn($linkMock);
        $linkMock->shouldReceive('getSubHref')->once()->withNoArgs()->andReturn($subUrl);

        $this->mock->shouldReceive('getUrlPrefix')->once()->withNoArgs()->andReturn($prefix);
    }

    /**
     * @param mixed  $meta
     * @param string $result
     *
     * @return void
     */
    private function willBeCalledEncoderForMeta($meta, $result)
    {
        $encoderMock = $this->willBeCalledGetEncoder(false);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeMeta')->once()->with($meta)->andReturn($result);
    }

    /**
     * @param mixed      $data
     * @param string     $result
     * @param array|null $links
     * @param mixed      $meta
     *
     * @return void
     */
    private function willBeCalledEncoderForIdentifiers($data, $result, array $links = null, $meta = null)
    {
        $encoderMock = $this->willBeCalledGetEncoder(true);

        if ($links !== null) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $encoderMock->shouldReceive('withLinks')->once()->with($links)->andReturnSelf();
        }

        if ($meta !== null) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $encoderMock->shouldReceive('withMeta')->once()->with($meta)->andReturnSelf();
        }

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeIdentifiers')
            ->once()
            ->withArgs([$data, $this->encodingencodingParameters])
            ->andReturn($result);
    }

    /**
     * @param mixed|Error $error
     * @param string      $result
     *
     * @return void
     */
    private function willBeCalledEncoderForError($error, $result)
    {
        $encoderMock = $this->willBeCalledGetEncoder(false);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeError')->once()->with($error)->andReturn($result);
    }

    /**
     * @param mixed|Error[] $errors
     * @param string        $result
     *
     * @return void
     */
    private function willBeCalledEncoderForErrors($errors, $result)
    {
        $encoderMock = $this->willBeCalledGetEncoder(false);

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $encoderMock->shouldReceive('encodeErrors')->once()->with($errors)->andReturn($result);
    }
}
