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

use Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;
use Neomerx\JsonApi\Contracts\Parameters\HeadersCheckerInterface;
use Neomerx\JsonApi\Contracts\Parameters\QueryCheckerInterface;
use Neomerx\JsonApi\Parameters\RestrictiveHeadersChecker;
use Neomerx\JsonApi\Parameters\RestrictiveParametersChecker;
use Neomerx\JsonApi\Parameters\RestrictiveQueryChecker;
use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Schema\Container;
use \Neomerx\JsonApi\Document\Document;
use \Neomerx\JsonApi\Encoder\Stack\Stack;
use \Neomerx\JsonApi\Encoder\Parser\Parser;
use \Neomerx\JsonApi\Parameters\Parameters;
use \Neomerx\JsonApi\Schema\ResourceObject;
use \Neomerx\JsonApi\Encoder\Stack\StackFrame;
use \Neomerx\JsonApi\Parameters\SortParameter;
use \Neomerx\JsonApi\Schema\RelationshipObject;
use \Neomerx\JsonApi\Encoder\Parser\ParserReply;
use \Neomerx\JsonApi\Encoder\Parser\DataAnalyzer;
use \Neomerx\JsonApi\Parameters\ParametersParser;
use \Neomerx\JsonApi\Encoder\Parser\ParserManager;
use \Neomerx\JsonApi\Parameters\Headers\MediaType;
use \Neomerx\JsonApi\Parameters\EncodingParameters;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Parameters\SupportedExtensions;
use \Neomerx\JsonApi\Encoder\Parser\ParserEmptyReply;
use \Neomerx\JsonApi\Parameters\Headers\AcceptHeader;
use \Neomerx\JsonApi\Encoder\Handlers\ReplyInterpreter;
use \Neomerx\JsonApi\Parameters\Headers\AcceptMediaType;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Schema\ResourceIdentifierSchemaAdapter;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Schema\ResourceIdentifierContainerAdapter;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\DataAnalyzerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Parameters\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFrameReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
class Factory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createDocument()
    {
        return new Document();
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
    public function createParser(DataAnalyzerInterface $analyzer, ParserManagerInterface $manager = null)
    {
        return new Parser($this, $this, $this, $analyzer, $manager);
    }

    /**
     * @inheritdoc
     */
    public function createManager(EncodingParametersInterface $parameters)
    {
        return new ParserManager($parameters);
    }

    /**
     * @inheritdoc
     */
    public function createFrame($level, StackFrameReadOnlyInterface $previous = null)
    {
        return new StackFrame($level, $previous);
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
    public function createReplyInterpreter(DocumentInterface $document, EncodingParametersInterface $parameters)
    {
        return new ReplyInterpreter($document, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function createAnalyzer(ContainerInterface $container)
    {
        return new DataAnalyzer($container);
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
        return new ParametersParser($this);
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
    public function createHeadersChecker(
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher
    ) {
        return new RestrictiveHeadersChecker($exceptionThrower, $codecMatcher);
    }

    /**
     * @inheritdoc
     */
    public function createQueryChecker(
        ExceptionThrowerInterface $exceptionThrower,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        return new RestrictiveQueryChecker(
            $exceptionThrower,
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
        HeadersCheckerInterface $headersChecker,
        QueryCheckerInterface $queryChecker
    ) {
        return new RestrictiveParametersChecker($headersChecker, $queryChecker);
    }

    /**
     * @inheritdoc
     */
    public function createContainer(array $providers = [])
    {
        return new Container($this, $providers);
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
}
