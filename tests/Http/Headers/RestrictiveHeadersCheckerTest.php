<?php namespace Neomerx\Tests\JsonApi\Http\Headers;

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
use \Neomerx\JsonApi\Http\Headers\MediaType;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiException;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeadersCheckerInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RestrictiveHeadersCheckerTest extends BaseTestCase
{
    /** JSON API type */
    const JSON_API_TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_TYPE;

    /** JSON API type */
    const SUB_TYPE = MediaTypeInterface::JSON_API_SUB_TYPE;

    /**
     * @var HeaderParametersParserInterface
     */
    private $parser;

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

        $this->parser      = (new Factory())->createHeaderParametersParser();
        $this->mockRequest = Mockery::mock(ServerRequestInterface::class);
    }

    /**
     * Test checker on default settings.
     */
    public function testDefaultNotReallyRestrictiveSettings()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse(
            $this->prepareRequest('POST', self::JSON_API_TYPE, self::JSON_API_TYPE)
        );

        $checker->checkHeaders($parameters);
    }

    /**
     * Test checker with allowed extensions.
     */
    public function testAllowedExtensions()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse($this->prepareRequest(
            'POST',
            self::JSON_API_TYPE.';ext=ext2',
            self::JSON_API_TYPE.';ext="ext1,ext3"'
        ));

        $checker->checkHeaders($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedInputExtensions()
    {
        $checker    = $this->getCheckerWithExtensions();
        $parameters = $this->parser->parse(
            $this->prepareRequest('POST', self::JSON_API_TYPE . ';ext=ext4', self::JSON_API_TYPE)
        );

        $exception  = null;
        try {
            $checker->checkHeaders($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_UNSUPPORTED_MEDIA_TYPE, $exception->getHttpCode());
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedOutputExtensions()
    {
        $checker    = $this->getCheckerWithExtensions();
        $parameters = $this->parser->parse($this->prepareRequest(
            'POST',
            self::JSON_API_TYPE,
            self::JSON_API_TYPE . ';ext="ext2,ext3"'
        ));

        $exception = null;
        try {
            $checker->checkHeaders($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_NOT_ACCEPTABLE, $exception->getHttpCode());
    }

    /**
     * Test check too many media types in 'Content-Type' header.
     */
    public function testTooManyMediaTypesInContentType()
    {
        $checker = $this->getCheckerWithExtensions();
        $parameters = $this->parser->parse($this->prepareRequest(
            'POST',
            self::JSON_API_TYPE.', one-more/media-type',
            self::JSON_API_TYPE
        ));

        $exception = null;
        try {
            $checker->checkHeaders($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * @param string $method
     * @param string $contentType
     * @param string $accept
     *
     * @return ServerRequestInterface
     */
    private function prepareRequest($method, $contentType, $accept)
    {
        $psr7Accept = empty($accept) === true ? [] : [$accept];
        $psr7ContentType = empty($contentType) === true ? [] : [$contentType];

        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getHeader')->with('Content-Type')->once()->andReturn($psr7ContentType);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getHeader')->with('Accept')->once()->andReturn($psr7Accept);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getMethod')->withNoArgs()->once()->andReturn($method);

        /** @var ServerRequestInterface $request */
        $request = $this->mockRequest;

        return $request;
    }

    /**
     * @param array $decoders
     * @param array $encoders
     *
     * @return CodecMatcherInterface
     */
    private function prepareCodecMatcher(array $decoders, array $encoders)
    {
        $matcher = (new Factory())->createCodecMatcher();
        $codecClosure = function () {
            return 'Codec result';
        };

        foreach ($decoders as list($type, $subType, $parameters)) {
            $mediaType = new MediaType($type, $subType, $parameters);
            $matcher->registerDecoder($mediaType, $codecClosure);
        }

        foreach ($encoders as list($type, $subType, $parameters)) {
            $mediaType = new MediaType($type, $subType, $parameters);
            $matcher->registerEncoder($mediaType, $codecClosure);
        }

        return $matcher;
    }

    /**
     * @return HeadersCheckerInterface
     */
    private function getCheckerWithExtensions()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [
                    [self::TYPE, self::SUB_TYPE, null],
                    [self::TYPE, self::SUB_TYPE, ['ext' => 'ext2']],
                ],
                [
                    [self::TYPE, self::SUB_TYPE, null],
                    [self::TYPE, self::SUB_TYPE, ['ext' => 'ext1,ext3']],
                ]
            )
        );

        return $checker;
    }

    /**
     * @param CodecMatcherInterface $codecMatcher
     *
     * @return HeadersCheckerInterface
     */
    private function getChecker(
        CodecMatcherInterface $codecMatcher
    ) {
        return (new Factory())->createHeadersChecker($codecMatcher);
    }
}
