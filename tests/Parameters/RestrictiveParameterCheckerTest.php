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
use \Exception;
use \Mockery\MockInterface;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Codec\CodecContainer;
use \Neomerx\JsonApi\Parameters\MediaType;
use \Neomerx\JsonApi\Parameters\ParametersFactory;
use \Neomerx\JsonApi\Parameters\RestrictiveParameterChecker;
use \Neomerx\JsonApi\Contracts\Codec\CodecContainerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Integration\CurrentRequestInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersParserInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RestrictiveParameterCheckerTest extends BaseTestCase
{
    /** JSON API type */
    const TYPE = CodecContainerInterface::JSON_API_TYPE;

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
        'sort'    => '-created,+title,name',
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
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext2']],
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext1,ext3']]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed extensions.
     */
    public function testAllowedExtensions()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext2']],
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext1,ext3']]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE.';ext=ext2', self::TYPE.';ext="ext1,ext3"', $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedInputExtensions()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions('throwUnsupportedMediaType'),
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext2']],
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext1,ext3']]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE.';ext=ext4', self::TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with not allowed extensions.
     */
    public function testNotAllowedOutputExtensions()
    {
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions('throwNotAcceptable'),
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext2']],
                [self::TYPE => [MediaTypeInterface::NO_EXT, 'ext1,ext3']]
            )
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE.';ext="ext2,ext3"', $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            ['author', 'comments', 'comments.author', 'and.one.more.path']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            ['author', 'comments']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            null,
            ['type1', 'anotherType']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            null,
            ['anotherType']
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
            $this->prepareExceptions()
        );

        $checker->check($parameters);
    }

    /**
     * Test checker with allowed search params.
     */
    public function testAllowedSearchParams()
    {
        $allowedSortParams = ['created', 'title', 'name', 'and-others'];
        $checker = new RestrictiveParameterChecker(
            $this->prepareExceptions(),
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false,
            null,
            null,
            $allowedSortParams
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(self::TYPE, self::TYPE, $this->requestParams),
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            true
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::TYPE,
                self::TYPE,
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
            $this->prepareCodecContainer(
                [self::TYPE => [MediaTypeInterface::NO_EXT]],
                [self::TYPE => [MediaTypeInterface::NO_EXT]]
            ),
            false
        );

        $parameters = $this->parser->parse(
            $this->prepareRequest(
                self::TYPE,
                self::TYPE,
                array_merge($this->requestParams, ['some' => ['other', 'parameters']])
            ),
            $this->prepareExceptions()
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
     * @return CodecContainerInterface
     */
    private function prepareCodecContainer(array $decoders, array $encoders)
    {
        $container = new CodecContainer();
        $codecClosure = function () {
            throw new Exception('Encoder or decoder should not be created during this test');
        };

        foreach ($decoders as $type => $extensions) {
            foreach ($extensions as $extensionsItem) {
                $mediaType = new MediaType($type, $extensionsItem);
                $container->registerDecoder($mediaType, $codecClosure);
            }
        }

        foreach ($encoders as $type => $extensions) {
            foreach ($extensions as $extensionsItem) {
                $mediaType = new MediaType($type, $extensionsItem);
                $container->registerEncoder($mediaType, $codecClosure);
            }
        }

        return $container;
    }
}
