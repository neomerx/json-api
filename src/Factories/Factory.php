<?php namespace Neomerx\JsonApi\Factories;

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

use \Closure;
use \Psr\Log\LoggerInterface;
use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Schema\Container;
use \Neomerx\JsonApi\Document\Document;
use \Neomerx\JsonApi\Codec\CodecMatcher;
use \Neomerx\JsonApi\Encoder\Stack\Stack;
use \Neomerx\JsonApi\Encoder\Parser\Parser;
use \Neomerx\JsonApi\Schema\IdentitySchema;
use \Neomerx\JsonApi\Schema\ResourceObject;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\JsonApi\Http\Headers\MediaType;
use \Neomerx\JsonApi\Encoder\Stack\StackFrame;
use \Neomerx\JsonApi\Http\Headers\AcceptHeader;
use \Neomerx\JsonApi\Schema\RelationshipObject;
use \Neomerx\JsonApi\Encoder\Parser\ParserReply;
use \Neomerx\JsonApi\Http\Parameters\Parameters;
use \Neomerx\JsonApi\Encoder\Parser\ParserManager;
use \Neomerx\JsonApi\Http\Headers\AcceptMediaType;
use \Neomerx\JsonApi\Http\Parameters\SortParameter;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Encoder\Parser\ParserEmptyReply;
use \Neomerx\JsonApi\Http\Parameters\ParametersParser;
use \Neomerx\JsonApi\Encoder\Handlers\ReplyInterpreter;
use \Neomerx\JsonApi\Http\Parameters\EncodingParameters;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Http\Parameters\SupportedExtensions;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use \Neomerx\JsonApi\Encoder\Parameters\ParametersAnalyzer;
use \Neomerx\JsonApi\Schema\ResourceIdentifierSchemaAdapter;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Http\Parameters\RestrictiveQueryChecker;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Http\Parameters\RestrictiveHeadersChecker;
use \Neomerx\JsonApi\Schema\ResourceIdentifierContainerAdapter;
use \Neomerx\JsonApi\Http\Parameters\RestrictiveParametersChecker;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;

/**
 * @package Neomerx\JsonApi
 */
class Factory implements FactoryInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->logger = new ProxyLogger();
    }

    /**
     * @inheritdoc
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger->setLogger($logger);
    }

    /**
     * @inheritdoc
     */
    public function createEncoder(ContainerInterface $container, EncoderOptions $encoderOptions = null)
    {
        $encoder = new Encoder($this, $container, $encoderOptions);

        $encoder->setLogger($this->logger);

        return $encoder;
    }

    /**
     * @inheritdoc
     */
    public function createDocument()
    {
        $document = new Document();

        $document->setLogger($this->logger);

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function createError(
        $idx = null,
        LinkInterface $aboutLink = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        $source = null,
        array $meta = null
    ) {
        return new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);
    }
    /**
     * @inheritdoc
     */
    public function createReply($replyType, StackReadOnlyInterface $stack)
    {
        return new ParserReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createEmptyReply(
        $replyType,
        StackReadOnlyInterface $stack
    ) {
        return new ParserEmptyReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createParser(ContainerInterface $container, ParserManagerInterface $manager)
    {
        $parser = new Parser($this, $this, $this, $container, $manager);

        $parser->setLogger($this->logger);

        return $parser;
    }

    /**
     * @inheritdoc
     */
    public function createManager(ParametersAnalyzerInterface $parameterAnalyzer)
    {
        $manager = new ParserManager($parameterAnalyzer);

        $manager->setLogger($this->logger);

        return $manager;
    }

    /**
     * @inheritdoc
     */
    public function createFrame(StackFrameReadOnlyInterface $previous = null)
    {
        return new StackFrame($previous);
    }

    /**
     * @inheritdoc
     */
    public function createStack()
    {
        return new Stack($this);
    }

    /**
     * @inheritdoc
     */
    public function createReplyInterpreter(DocumentInterface $document, ParametersAnalyzerInterface $parameterAnalyzer)
    {
        $interpreter = new ReplyInterpreter($document, $parameterAnalyzer);

        $interpreter->setLogger($this->logger);

        return $interpreter;
    }

    /**
     * @inheritdoc
     */
    public function createEncodingParameters($includePaths = null, array $fieldSets = null)
    {
        return new EncodingParameters($includePaths, $fieldSets);
    }

    /**
     * @inheritdoc
     */
    public function createParametersAnalyzer(EncodingParametersInterface $parameters, ContainerInterface $container)
    {
        $analyzer = new ParametersAnalyzer($parameters, $container);

        $analyzer->setLogger($this->logger);

        return $analyzer;
    }

    /**
     * @inheritdoc
     */
    public function createMediaType($type, $subType, $parameters = null)
    {
        return new MediaType($type, $subType, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function createParameters(
        HeaderInterface $contentType,
        AcceptHeaderInterface $accept,
        $includePaths = null,
        array $fieldSets = null,
        $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null,
        array $unrecognizedParams = null
    ) {
        return new Parameters(
            $contentType,
            $accept,
            $includePaths,
            $fieldSets,
            $sortParameters,
            $pagingParameters,
            $filteringParameters,
            $unrecognizedParams
        );
    }

    /**
     * @inheritdoc
     */
    public function createParametersParser()
    {
        $parser = new ParametersParser($this);

        $parser->setLogger($this->logger);

        return $parser;
    }

    /**
     * @inheritdoc
     */
    public function createSortParam($sortField, $isAscending)
    {
        return new SortParameter($sortField, $isAscending);
    }

    /**
     * @inheritdoc
     */
    public function createSupportedExtensions($extensions = MediaTypeInterface::NO_EXT)
    {
        return new SupportedExtensions($extensions);
    }

    /**
     * @inheritdoc
     */
    public function createAcceptMediaType(
        $position,
        $type,
        $subType,
        $parameters = null,
        $quality = 1.0,
        $extensions = null
    ) {
        return new AcceptMediaType($position, $type, $subType, $parameters, $quality, $extensions);
    }

    /**
     * @inheritdoc
     */
    public function createAcceptHeader($unsortedMediaTypes)
    {
        return new AcceptHeader($unsortedMediaTypes);
    }

    /**
     * @inheritdoc
     */
    public function createHeadersChecker(CodecMatcherInterface $codecMatcher)
    {
        return new RestrictiveHeadersChecker($codecMatcher);
    }

    /**
     * @inheritdoc
     */
    public function createQueryChecker(
        $allowUnrecognized = true,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        return new RestrictiveQueryChecker(
            $allowUnrecognized,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );
    }

    /**
     * @inheritdoc
     */
    public function createParametersChecker(
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $headersChecker = $this->createHeadersChecker($codecMatcher);
        $queryChecker   = $this->createQueryChecker(
            $allowUnrecognized,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );

        return new RestrictiveParametersChecker($headersChecker, $queryChecker);
    }

    /**
     * @inheritdoc
     */
    public function createContainer(array $providers = [])
    {
        $container = new Container($this, $providers);

        $container->setLogger($this->logger);

        return $container;
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject(
        SchemaProviderInterface $schema,
        $resource,
        $isInArray,
        $attributeKeysFilter = null
    ) {
        return new ResourceObject($schema, $resource, $isInArray, $attributeKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipObject($name, $data, $links, $meta, $isShowData, $isRoot)
    {
        return new RelationshipObject($name, $data, $links, $meta, $isShowData, $isRoot);
    }

    /**
     * @inheritdoc
     */
    public function createLink($subHref, $meta = null, $treatAsHref = false)
    {
        return new Link($subHref, $meta, $treatAsHref);
    }

    /**
     * @inheritdoc
     */
    public function createResourceIdentifierSchemaAdapter(SchemaProviderInterface $schema)
    {
        return new ResourceIdentifierSchemaAdapter($this, $schema);
    }

    /**
     * @inheritdoc
     */
    public function createResourceIdentifierContainerAdapter(ContainerInterface $container)
    {
        return new ResourceIdentifierContainerAdapter($this, $container);
    }

    /**
     * @inheritdoc
     */
    public function createIdentitySchema(ContainerInterface $container, $classType, Closure $identityClosure)
    {
        return new IdentitySchema($this, $container, $classType, $identityClosure);
    }

    /**
     * @inheritdoc
     */
    public function createCodecMatcher()
    {
        return new CodecMatcher();
    }
}
