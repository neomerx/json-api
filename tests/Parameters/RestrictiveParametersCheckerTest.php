<?php namespace Neomerx\Tests\JsonApi\Parameters;

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
use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Parameters\Headers\MediaType;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Parameters\RestrictiveParametersChecker;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;

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
     * @var MockInterface
     */
    private $mockThrower;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser      = (new Factory())->createParametersParser();
        $this->mockRequest = Mockery::mock(ServerRequestInterface::class);
        $this->mockThrower = Mockery::mock(ExceptionThrowerInterface::class);
    }

    /**
     * Test checker on default settings.
     */
    public function testDefaultNotReallyRestrictiveSettings()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed extensions.
     */
    public function testAllowedExtensions()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE.';ext=ext2',
                self::JSON_API_TYPE.';ext="ext1,ext3"',
                $this->requestParams
            ),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedInputExtensions()
    {
        $checker = $this->getCheckerWithExtensions('throwUnsupportedMediaType');

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE.';ext=ext4', self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedOutputExtensions()
    {
        $checker = $this->getCheckerWithExtensions('throwNotAcceptable');

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE.';ext="ext2,ext3"', $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed input paths.
     */
    public function testAllowedInputPaths()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            ['author', 'comments', 'comments.author', 'and.one.more.path']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed input paths.
     */
    public function testNotAllowedInputPaths()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions('throwBadRequest'),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            ['author', 'comments']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed field sets.
     */
    public function testAllowedFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => ['fields1', 'fields2', 'fields3'],]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed field sets.
     */
    public function testAllowedAllFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => null] // all fields are allowed for type1
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed type in field sets.
     */
    public function testNonEsistingFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['nonExistingType' => null]
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions('throwBadRequest')
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed field sets.
     */
    public function testNotAllowedFieldSets()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions('throwBadRequest'),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1' => ['fields1']] // only 1 allowed field (2 in request)
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed search params.
     */
    public function testAllowedSearchParams()
    {
        $allowedSortParams = ['created', 'title', 'name.with.dots', 'and-others'];
        $checker = $this->getChecker(
            $this->prepareExceptions(),
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
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
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
            $this->prepareExceptions('throwBadRequest', 2), // expect just at least one 'bad request'
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
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed unrecognized parameters.
     */
    public function testAllowedUnrecognizedParameters()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions(),
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
            ),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed unrecognized parameters.
     */
    public function testNotAllowedUnrecognizedParameters()
    {
        $checker = $this->getChecker(
            $this->prepareExceptions('throwBadRequest'),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE,
                self::JSON_API_TYPE,
                array_merge($this->requestParams, ['some' => ['other', 'parameters']])
            ),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test check too many media types in 'Content-Type' header.
     */
    public function testTooManyMediaTypesInContentType()
    {
        $checker = $this->getCheckerWithExtensions();

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE.', one-more/media-type',
                self::JSON_API_TYPE,
                $this->requestParams
            ),
            $this->prepareExceptions('throwBadRequest')
        );

        $checker->check($parameters);
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
     * @param string $exceptionMethod
     * @param int    $times
     *
     * @return ExceptionThrowerInterface
     */
    private function prepareExceptions($exceptionMethod = null, $times = 1)
    {
        if ($exceptionMethod !== null) {
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $this->mockThrower->shouldReceive($exceptionMethod)->times($times)->withNoArgs()->andReturnUndefined();
        }

        /** @var ExceptionThrowerInterface $exceptions */
        $exceptions = $this->mockThrower;

        return $exceptions;
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
     * @param string|null $exceptionMethod
     *
     * @return RestrictiveParametersChecker
     */
    private function getCheckerWithExtensions($exceptionMethod = null)
    {
        $checker = $this->getChecker(
            $this->prepareExceptions($exceptionMethod),
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
     * @param ExceptionThrowerInterface $exceptionThrower
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
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        return (new Factory())->createParametersChecker(
            $exceptionThrower,
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
