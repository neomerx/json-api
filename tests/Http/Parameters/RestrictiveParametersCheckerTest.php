<?php namespace Neomerx\Tests\JsonApi\Http\Parameters;

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
use \Mockery\MockInterface;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Http\Headers\MediaType;
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Exceptions\JsonApiException;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Http\Parameters\RestrictiveParametersChecker;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersParserInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RestrictiveParametersCheckerTest extends BaseTestCase
{
    /** JSON API type */
    const JSON_API_TYPE = MediaTypeInterface::JSON_API_MEDIA_TYPE;

    /** JSON API type */
    const TYPE = MediaTypeInterface::JSON_API_TYPE;

    /** JSON API type */
    const SUB_TYPE = MediaTypeInterface::JSON_API_SUB_TYPE;

    /**
     * @var ParametersParserInterface
     */
    private $parser;

    /**
     * @var array
     */
    private $requestParams = [
        ParametersParserInterface::PARAM_FIELDS  => ['type1' => 'fields1,fields2'],
        ParametersParserInterface::PARAM_INCLUDE => 'author,comments,comments.author',
        ParametersParserInterface::PARAM_SORT    => '-created,+title,name.with.dots',
        ParametersParserInterface::PARAM_FILTER  => ['some' => 'filter'],
        ParametersParserInterface::PARAM_PAGE    => ['size' => 10, 'offset' => 4],
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

        $this->parser      = (new Factory())->createParametersParser();
        $this->mockRequest = Mockery::mock(ServerRequestInterface::class);
    }

    /**
     * Test checker on default settings.
     */
    public function testDefaultNotReallyRestrictiveSettings()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed extensions.
     */
    public function testAllowedExtensions()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse($this->prepareRequest(
            self::JSON_API_TYPE.';ext=ext2',
            self::JSON_API_TYPE.';ext="ext1,ext3"',
            $this->requestParams
        ));

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedInputExtensions()
    {
        $checker    = $this->getCheckerWithExtensions();
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE . ';ext=ext4', self::JSON_API_TYPE, $this->requestParams)
        );

        $exception  = null;
        try {
            $checker->check($parameters);
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
            self::JSON_API_TYPE,
            self::JSON_API_TYPE . ';ext="ext2,ext3"',
            $this->requestParams
        ));

        $exception = null;
        try {
            $checker->check($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_NOT_ACCEPTABLE, $exception->getHttpCode());
    }

    /**
     * Test checker with allowed input paths.
     */
    public function testAllowedInputPaths()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            ['author', 'comments', 'comments.author', 'and.one.more.path']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed input paths.
     */
    public function testNotAllowedInputPaths()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            ['author', 'comments']
        );
        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $exception = null;
        try {
            $checker->check($parameters);
        } catch (JsonApiException $exception) {
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
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => ['fields1', 'fields2', 'fields3'],]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed field sets.
     */
    public function testAllowedAllFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => null] // all fields are allowed for type1
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed type in field sets.
     */
    public function testNonEsistingFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['nonExistingType' => null]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $exception = null;
        try {
            $checker->check($parameters);
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
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => ['fields1']] // only 1 allowed field (2 in request)
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $exception = null;
        try {
            $checker->check($parameters);
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
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed search params.
     */
    public function testNotAllowedSearchParams()
    {
        $allowedSortParams = ['created', 'name']; // in input will be 'title' which is not on the list
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams)
        );

        $exception = null;
        try {
            $checker->check($parameters);
        } catch (JsonApiException $exception) {
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
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            true
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE,
                self::JSON_API_TYPE,
                array_merge($this->requestParams, ['some' => ['other', 'parameters']])
            )
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed unrecognized parameters.
     */
    public function testNotAllowedUnrecognizedParameters()
    {
        $checker = $this->getChecker(
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false
        );

        $parameters = $this->parser->parse($this->prepareRequest(
            self::JSON_API_TYPE,
            self::JSON_API_TYPE,
            array_merge($this->requestParams, ['some' => ['other', 'parameters']])
        ));

        $exception = null;
        try {
            $checker->check($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * Test check too many media types in 'Content-Type' header.
     */
    public function testTooManyMediaTypesInContentType()
    {
        $checker = $this->getCheckerWithExtensions();
        $parameters = $this->parser->parse($this->prepareRequest(
            self::JSON_API_TYPE.', one-more/media-type',
            self::JSON_API_TYPE,
            $this->requestParams
        ));

        $exception = null;
        try {
            $checker->check($parameters);
        } catch (JsonApiException $exception) {
        }

        $this->assertNotNull($exception);
        $this->assertEquals(JsonApiException::HTTP_CODE_BAD_REQUEST, $exception->getHttpCode());
    }

    /**
     * @param string $contentType
     * @param string $accept
     * @param array  $input
     *
     * @return ServerRequestInterface
     */
    private function prepareRequest($contentType, $accept, array $input)
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getHeader')->with('Content-Type')->once()->andReturn($contentType);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getHeader')->with('Accept')->once()->andReturn($accept);
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        $this->mockRequest->shouldReceive('getQueryParams')->withNoArgs()->once()->andReturn($input);

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
     * @return RestrictiveParametersChecker
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
     * @param CodecMatcherInterface     $codecMatcher
     * @param bool|false                $allowUnrecognized
     * @param array|null                $includePaths
     * @param array|null                $fieldSetTypes
     * @param array|null                $sortParameters
     * @param array|null                $pagingParameters
     * @param array|null                $filteringParameters
     *
     * @return RestrictiveParametersChecker
     */
    private function getChecker(
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        return (new Factory())->createParametersChecker(
            $codecMatcher,
            $allowUnrecognized,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );
    }
}
