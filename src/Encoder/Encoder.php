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
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\DataAnalyzerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\EncodingParametersInterface;

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
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * @var EncoderOptions|null
     */
    protected $encoderOptions;

    /**
     * @param FactoryInterface    $factory
     * @param array               $schemas
     * @param EncoderOptions|null $encoderOptions
     */
    public function __construct(FactoryInterface $factory, array $schemas, EncoderOptions $encoderOptions = null)
    {
        $this->factory        = $factory;
        $this->container      = $factory->createContainer($schemas);
        $this->encoderOptions = $encoderOptions;
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
        $dataAnalyzer  = $this->factory->createAnalyzer($this->container);

        $parameters    = $this->getEncodingParameters($data, $dataAnalyzer, $parameters);

        $docWriter     = $this->factory->createDocument();
        $parserManager = $this->factory->createManager($parameters);
        $parser        = $this->factory->createParser($dataAnalyzer, $parserManager);
        $interpreter   = $this->factory->createReplyInterpreter($docWriter, $parameters);

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
        $docWriter = $this->factory->createDocument();

        $docWriter->addError($error);

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function errors($errors)
    {
        $docWriter = $this->factory->createDocument();
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
        $docWriter = $this->factory->createDocument();

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
        return new self(static::getFactory(), $schemas, $encodeOptions);
    }

    /**
     * @return FactoryInterface
     */
    protected static function getFactory()
    {
        return new Factory();
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

        if ($isDataEmpty === true) {
            return $this->factory->createEncodingParameters();
        } elseif ($parameters !== null && $parameters->getIncludePaths() !== null) {
            return $parameters;
        } else {
            $includePaths = $schema->getIncludePaths();
            $fieldSets    = $parameters === null ? null : $parameters->getFieldSets();

            return $this->factory->createEncodingParameters($includePaths, $fieldSets);
        }
    }
}
