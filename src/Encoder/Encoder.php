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

use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Parameters\ParametersFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface;
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
     * @var JsonEncodeOptions|null
     */
    protected $encodeOptions;

    /**
     * @param DocumentFactoryInterface   $documentFactory
     * @param ParserFactoryInterface     $parserFactory
     * @param HandlerFactoryInterface    $handlerFactory
     * @param ParametersFactoryInterface $parametersFactory
     * @param ContainerInterface         $container
     * @param JsonEncodeOptions|null     $encodeOptions
     */
    public function __construct(
        DocumentFactoryInterface $documentFactory,
        ParserFactoryInterface $parserFactory,
        HandlerFactoryInterface $handlerFactory,
        ParametersFactoryInterface $parametersFactory,
        ContainerInterface $container,
        JsonEncodeOptions $encodeOptions = null
    ) {
        $this->container         = $container;
        $this->encodeOptions     = $encodeOptions;
        $this->parserFactory     = $parserFactory;
        $this->handlerFactory    = $handlerFactory;
        $this->documentFactory   = $documentFactory;
        $this->parametersFactory = $parametersFactory;
    }

    /**
     * @inheritdoc
     */
    public function encode(
        $data,
        DocumentLinksInterface $links = null,
        $meta = null,
        EncodingParametersInterface $parameters = null
    ) {
        $docWriter     = $this->documentFactory->createDocument();
        $parameters    = $this->getEncodingParameters($data, $parameters);
        $parserManager = $this->parserFactory->createManager($parameters);
        $parser        = $this->parserFactory->createParser($this->container, $parserManager);
        $interpreter   = $this->handlerFactory->createReplyInterpreter($docWriter, $parameters);
        foreach ($parser->parse($data) as $reply) {
            $interpreter->handle($reply);
        }

        $meta  === null ?: $docWriter->setMetaToDocument($meta);
        $links === null ?: $docWriter->setDocumentLinks($links);

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
     * Encode array to JSON.
     *
     * @param array $document
     *
     * @return string
     */
    protected function encodeToJson(array $document)
    {
        return $this->encodeOptions === null ?
            json_encode($document) :
            json_encode($document, $this->encodeOptions->getOptions(), $this->encodeOptions->getDepth());
    }

    /**
     * Create encoder instance.
     *
     * @param array                  $schemas       Schema providers.
     * @param JsonEncodeOptions|null $encodeOptions
     *
     * @return Encoder
     */
    public static function instance(array $schemas, JsonEncodeOptions $encodeOptions = null)
    {
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $schemaFactory = new \Neomerx\JsonApi\Schema\SchemaFactory();
        $container     = $schemaFactory->createContainer($schemas);
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $documentFactory = new \Neomerx\JsonApi\Document\DocumentFactory();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $encoderFactory = new \Neomerx\JsonApi\Encoder\Factory\EncoderFactory();
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        $parameterFactory = new \Neomerx\JsonApi\Parameters\ParametersFactory();

        return new self(
            $documentFactory,
            $encoderFactory,
            $encoderFactory,
            $parameterFactory,
            $container,
            $encodeOptions
        );
    }

    /**
     * @param array|object|null                $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return EncodingParametersInterface
     */
    private function getEncodingParameters($data, EncodingParametersInterface $parameters = null)
    {
        if (empty($data) === true && $parameters === null) {
            return $this->parametersFactory->createEncodingParameters();
        } elseif ($parameters !== null && $parameters->getIncludePaths() !== null) {
            return $parameters;
        } else {
            $schema       = $this->container->getSchema(is_array($data) ? $data[0] : $data);
            $includePaths = $schema->getIncludePaths();
            $fieldSets    = $parameters === null ? null : $parameters->getFieldSets();

            return $this->parametersFactory->createEncodingParameters($includePaths, $fieldSets);
        }
    }
}
