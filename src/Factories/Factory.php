<?php namespace Neomerx\JsonApi\Factories;

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

use Closure;
use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Handlers\ReplyInterpreterInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parser\ParserReplyInterface;
use Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameInterface;
use Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;
use Neomerx\JsonApi\Contracts\Encoder\Stack\StackInterface;
use Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Document\Document;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Encoder\EncoderOptions;
use Neomerx\JsonApi\Encoder\Handlers\ReplyInterpreter;
use Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;
use Neomerx\JsonApi\Encoder\Parameters\ParametersAnalyzer;
use Neomerx\JsonApi\Encoder\Parser\Parser;
use Neomerx\JsonApi\Encoder\Parser\ParserEmptyReply;
use Neomerx\JsonApi\Encoder\Parser\ParserManager;
use Neomerx\JsonApi\Encoder\Parser\ParserReply;
use Neomerx\JsonApi\Encoder\Stack\Stack;
use Neomerx\JsonApi\Encoder\Stack\StackFrame;
use Neomerx\JsonApi\Http\Headers\AcceptMediaType;
use Neomerx\JsonApi\Http\Headers\HeaderParametersParser;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\JsonApi\Schema\Container;
use Neomerx\JsonApi\Schema\IdentitySchema;
use Neomerx\JsonApi\Schema\RelationshipObject;
use Neomerx\JsonApi\Schema\ResourceIdentifierContainerAdapter;
use Neomerx\JsonApi\Schema\ResourceIdentifierSchemaAdapter;
use Neomerx\JsonApi\Schema\ResourceObject;
use Psr\Log\LoggerInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger->setLogger($logger);
    }

    /**
     * @inheritdoc
     */
    public function createEncoder(
        ContainerInterface $container,
        EncoderOptions $encoderOptions = null
    ): EncoderInterface {
        $encoder = new Encoder($this, $container, $encoderOptions);

        $encoder->setLogger($this->logger);

        return $encoder;
    }

    /**
     * @inheritdoc
     */
    public function createDocument(): DocumentInterface
    {
        $document = new Document();

        $document->setLogger($this->logger);

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function createError(
        string $idx = null,
        LinkInterface $aboutLink = null,
        string $status = null,
        string $code = null,
        string $title = null,
        string $detail = null,
        array $source = null,
        array $meta = null
    ): ErrorInterface {
        return new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);
    }

    /**
     * @inheritdoc
     */
    public function createReply(int $replyType, StackReadOnlyInterface $stack): ParserReplyInterface
    {
        return new ParserReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createEmptyReply(int $replyType, StackReadOnlyInterface $stack): ParserReplyInterface
    {
        return new ParserEmptyReply($replyType, $stack);
    }

    /**
     * @inheritdoc
     */
    public function createParser(ContainerInterface $container, ParserManagerInterface $manager): ParserInterface
    {
        $parser = new Parser($this, $this, $this, $container, $manager);

        $parser->setLogger($this->logger);

        return $parser;
    }

    /**
     * @inheritdoc
     */
    public function createManager(ParametersAnalyzerInterface $parameterAnalyzer): ParserManagerInterface
    {
        $manager = new ParserManager($parameterAnalyzer);

        $manager->setLogger($this->logger);

        return $manager;
    }

    /**
     * @inheritdoc
     */
    public function createFrame(StackFrameReadOnlyInterface $previous = null): StackFrameInterface
    {
        return new StackFrame($previous);
    }

    /**
     * @inheritdoc
     */
    public function createStack(): StackInterface
    {
        return new Stack($this);
    }

    /**
     * @inheritdoc
     */
    public function createReplyInterpreter(
        DocumentInterface $document,
        ParametersAnalyzerInterface $parameterAnalyzer
    ): ReplyInterpreterInterface {
        $interpreter = new ReplyInterpreter($document, $parameterAnalyzer);

        $interpreter->setLogger($this->logger);

        return $interpreter;
    }

    /**
     * @inheritdoc
     */
    public function createParametersAnalyzer(
        EncodingParametersInterface $parameters,
        ContainerInterface $container
    ): ParametersAnalyzerInterface {
        $analyzer = new ParametersAnalyzer($parameters, $container);

        $analyzer->setLogger($this->logger);

        return $analyzer;
    }

    /**
     * @inheritdoc
     */
    public function createMediaType(string $type, string $subType, array $parameters = null): MediaTypeInterface
    {
        return new MediaType($type, $subType, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function createQueryParameters(
        array $includePaths = null,
        array $fieldSets = null
    ): EncodingParametersInterface {
        return new EncodingParameters($includePaths, $fieldSets);
    }

    /**
     * @inheritdoc
     */
    public function createHeaderParametersParser(): HeaderParametersParserInterface
    {
        $parser = new HeaderParametersParser($this);

        $parser->setLogger($this->logger);

        return $parser;
    }

    /**
     * @inheritdoc
     */
    public function createAcceptMediaType(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0
    ): AcceptMediaTypeInterface {
        return new AcceptMediaType($position, $type, $subType, $parameters, $quality);
    }

    /**
     * @inheritdoc
     */
    public function createContainer(array $providers = []): ContainerInterface
    {
        $container = new Container($this, $providers);

        $container->setLogger($this->logger);

        return $container;
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject(
        SchemaInterface $schema,
        $resource,
        bool $isInArray,
        array $fieldKeysFilter = null
    ): ResourceObjectInterface {
        return new ResourceObject($schema, $resource, $isInArray, $fieldKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipObject(
        ?string $name,
        $data,
        array $links,
        $meta,
        bool $isShowData,
        bool $isRoot
    ): RelationshipObjectInterface {
        return new RelationshipObject($name, $data, $links, $meta, $isShowData, $isRoot);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function createLink(string $subHref, $meta = null, bool $treatAsHref = false): LinkInterface
    {
        return new Link($subHref, $meta, $treatAsHref);
    }

    /**
     * @inheritdoc
     */
    public function createResourceIdentifierSchemaAdapter(SchemaInterface $schema): SchemaInterface
    {
        return new ResourceIdentifierSchemaAdapter($this, $schema);
    }

    /**
     * @inheritdoc
     */
    public function createResourceIdentifierContainerAdapter(ContainerInterface $container): ContainerInterface
    {
        return new ResourceIdentifierContainerAdapter($this, $container);
    }

    /**
     * @inheritdoc
     */
    public function createIdentitySchema(
        ContainerInterface $container,
        string $classType,
        Closure $identityClosure
    ): SchemaInterface {
        return new IdentitySchema($this, $container, $classType, $identityClosure);
    }
}
