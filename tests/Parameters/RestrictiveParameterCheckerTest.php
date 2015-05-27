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
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Codec\CodecMatcher;
use \Neomerx\JsonApi\Parameters\Headers\MediaType;
use \Neomerx\JsonApi\Parameters\ParametersFactory;
use \Neomerx\JsonApi\Parameters\RestrictiveParameterChecker;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RestrictiveParameterCheckerTest extends BaseTestCase
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
        'fields'  => ['type1' => 'fields1,fields2'],
        'include' => 'author,comments,comments.author',
        'sort'    => '-created,+title,name.with.dots',
        'filter'  => ['some' => 'filter'],
        'page'    => ['size' => 10, 'offset' => 4],
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

        $this->parser      = (new ParametersFactory())->createParametersParser();
        $this->mockRequest = Mockery::mock(CurrentRequestInterface::class);
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
        $checker = new RestrictiveParameterChecker(
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
        $checker = new RestrictiveParameterChecker(
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
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['type1', 'anotherType']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::JSON_API_TYPE, self::JSON_API_TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed field sets.
     */
    public function testNotAllowedFieldSets()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions('throwBadRequest'),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null]],
                [[self::TYPE, self::SUB_TYPE, null]]
            ),
            false,
            null,
            ['anotherType']
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
        $checker = new RestrictiveParameterChecker(
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
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions('throwBadRequest'),
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
        $checker = new RestrictiveParameterChecker(
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
        $checker = new RestrictiveParameterChecker(
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
     * Test it should throw exception if 'ext' param is set but extensions are not allowed.
     */
    public function testExtentionsNotAllowed1()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, [MediaTypeInterface::PARAM_EXT => 'foo']],],
                [[self::TYPE, self::SUB_TYPE, null],]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE . ';' . MediaTypeInterface::PARAM_EXT . '=foo',
                self::JSON_API_TYPE,
                $this->requestParams
            ),
            $this->prepareExceptions('throwUnsupportedMediaType')
        );

        $checker->check($parameters);
    }

    /**
     * Test it should throw exception if 'ext' param is set but extensions are not allowed.
     */
    public function testExtentionsNotAllowed2()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecMatcher(
                [[self::TYPE, self::SUB_TYPE, null],],
                [[self::TYPE, self::SUB_TYPE, [MediaTypeInterface::PARAM_EXT => 'foo']],]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::JSON_API_TYPE,
                self::JSON_API_TYPE . ';' . MediaTypeInterface::PARAM_EXT . '=foo',
                $this->requestParams
            ),
            $this->prepareExceptions('throwNotAcceptable')
        );

        $checker->check($parameters);
    }

    /**
     * @param string $contentType
     * @param string $accept
     * @param array  $input
     *
     * @return CurrentRequestInterface
     */
    private function prepareRequest($contentType, $accept, array $input)
    {
        $this->mockRequest->shouldReceive('getHeader')->with('Content-Type')->once()->andReturn($contentType);
        $this->mockRequest->shouldReceive('getHeader')->with('Accept')->once()->andReturn($accept);
        $this->mockRequest->shouldReceive('getQueryParameters')->withNoArgs()->once()->andReturn($input);

        /** @var CurrentRequestInterface $request */
        $request = $this->mockRequest;

        return $request;
    }

    /**
     * @param string $exceptionMethod
     *
     * @return ExceptionThrowerInterface
     */
    private function prepareExceptions($exceptionMethod = null)
    {
        if ($exceptionMethod !== null) {
            $this->mockThrower->shouldReceive($exceptionMethod)->atLeast(1)->withNoArgs()->andReturnUndefined();
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
        $matcher = new CodecMatcher();
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
     * @return RestrictiveParameterChecker
     */
    private function getCheckerWithExtensions($exceptionMethod = null)
    {
        $checker = new RestrictiveParameterChecker(
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
            ),
            false,
            null,
            null,
            null,
            null,
            null,
            true
        );

        return $checker;
    }
}
