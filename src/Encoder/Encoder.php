<?php namespace Neomerx\JsonApi\Encoder;

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

use \Iterator;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\DataAnalyzerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Handlers\HandlerFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class Encoder implements EncoderInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DocumentFactoryInterface
     */
    protected $documentFactory;

    /**
     * @var ParserFactoryInterface
     */
    private $parserFactory;

    /**
     * @var HandlerFactoryInterface
     */
    private $handlerFactory;

    /**
     * @var ParametersFactoryInterface
     */
    private $parametersFactory;

    /**
     * @var EncoderOptions|null
     */
    protected $encoderOptions;

    /**
     * @param DocumentFactoryInterface   $documentFactory
     * @param ParserFactoryInterface     $parserFactory
     * @param HandlerFactoryInterface    $handlerFactory
     * @param ParametersFactoryInterface $parametersFactory
     * @param ContainerInterface         $container
     * @param EncoderOptions|null        $encoderOptions
     */
    public function __construct(
        DocumentFactoryInterface $documentFactory,
        ParserFactoryInterface $parserFactory,
        HandlerFactoryInterface $handlerFactory,
        ParametersFactoryInterface $parametersFactory,
        ContainerInterface $container,
        EncoderOptions $encoderOptions = null
    ) {
        $this->container         = $container;
        $this->parserFactory     = $parserFactory;
        $this->handlerFactory    = $handlerFactory;
        $this->encoderOptions    = $encoderOptions;
        $this->documentFactory   = $documentFactory;
        $this->parametersFactory = $parametersFactory;
    }

    /**
     * @inheritdoc
     */
    public function encode(
        $data,
        $links = null,
        $meta = null,
        EncodingParametersInterface $parameters = null
    ) {
        $dataAnalyzer  = $this->parserFactory->createAnalyzer($this->container);

        $parameters    = $this->getEncodingParameters($data, $dataAnalyzer, $parameters);

        $docWriter     = $this->documentFactory->createDocument();
        $parserManager = $this->parserFactory->createManager($parameters);
        $parser        = $this->parserFactory->createParser($dataAnalyzer, $parserManager);
        $interpreter   = $this->handlerFactory->createReplyInterpreter($docWriter, $parameters);

        $this->encoderOptions !== null && $this->encoderOptions->getUrlPrefix() !== null ?
            $docWriter->setUrlPrefix($this->encoderOptions->getUrlPrefix()) : null;

        foreach ($parser->parse($data) as $reply) {
            $interpreter->handle($reply);
        }

        $meta  === null ?: $docWriter->setMetaToDocument($meta);
        $links === null ?: $docWriter->setDocumentLinks($links);

        if ($this->encoderOptions !== null && $this->encoderOptions->isShowVersionInfo() === true) {
            $docWriter->addJsonApiVersion(self::JSON_API_VERSION, $this->encoderOptions->getVersionMeta());
        }

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function error(ErrorInterface $error)
    {
        $docWriter = $this->documentFactory->createDocument();

        $docWriter->addError($error);

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function errors($errors)
    {
        $docWriter = $this->documentFactory->createDocument();
        foreach ($errors as $error) {
            assert('$error instanceof '.ErrorInterface::class);
            $docWriter->addError($error);
        }

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function meta($meta)
    {
        $docWriter = $this->documentFactory->createDocument();

        $docWriter->setMetaToDocument($meta);
        $docWriter->unsetData();

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function getEncoderOptions()
    {
        return $this->encoderOptions;
    }

    /**
     * Encode array to JSON.
     *
     * @param array $document
     *
     * @return string
     */
    protected function encodeToJson(array $document)
    {
        return $this->encoderOptions === null ?
            json_encode($document) :
            json_encode($document, $this->encoderOptions->getOptions(), $this->encoderOptions->getDepth());
    }

    /**
     * Create encoder instance.
     *
     * @param array               $schemas       Schema providers.
     * @param EncoderOptions|null $encodeOptions
     *
     * @return Encoder
     */
    public static function instance(array $schemas, EncoderOptions $encodeOptions = null)
    {
        /** @var SchemaFactoryInterface $schemaFactory */
        /** @var DocumentFactoryInterface $documentFactory */
        /** @var ParserFactoryInterface $parserFactory */
        /** @var HandlerFactoryInterface $handlerFactory */
        /** @var ParametersFactoryInterface $parameterFactory */
        list($schemaFactory, $documentFactory, $parserFactory, $handlerFactory, $parameterFactory) =
            static::getFactories();

        $container = $schemaFactory->createContainer($schemas);

        return new self(
            $documentFactory,
            $parserFactory,
            $handlerFactory,
            $parameterFactory,
            $container,
            $encodeOptions
        );
    }

    /**
     * @return array [$schemaFactory, $documentFactory, $parserFactory, $handlerFactory, $parameterFactory]
     */
    protected static function getFactories()
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $schemaFactory = new \Neomerx\JsonApi\Schema\SchemaFactory();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $documentFactory = new \Neomerx\JsonApi\Document\DocumentFactory();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $parserFactory = $handlerFactory = new \Neomerx\JsonApi\Encoder\Factory\EncoderFactory();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $parameterFactory = new \Neomerx\JsonApi\Parameters\ParametersFactory();

        return [$schemaFactory, $documentFactory, $parserFactory, $handlerFactory, $parameterFactory];
    }

    /**
     * @param object|array|Iterator|null       $data
     * @param DataAnalyzerInterface            $analyzer
     * @param EncodingParametersInterface|null $parameters
     *
     * @return EncodingParametersInterface
     */
    private function getEncodingParameters($data, DataAnalyzerInterface $analyzer, $parameters = null)
    {
        /** @var bool $isDataEmpty */
        /** @var SchemaProviderInterface $schema */
        list($isDataEmpty, , $schema) = $analyzer->analyze($data);

        if ($isDataEmpty === true && $parameters === null) {
            return $this->parametersFactory->createEncodingParameters();
        } elseif ($parameters !== null && $parameters->getIncludePaths() !== null) {
            return $parameters;
        } else {
            $includePaths = $schema->getIncludePaths();
            $fieldSets    = $parameters === null ? null : $parameters->getFieldSets();

            return $this->parametersFactory->createEncodingParameters($includePaths, $fieldSets);
        }
    }
}
