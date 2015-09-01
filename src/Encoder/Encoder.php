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
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
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
     * Links in array<string,LinkInterface> format.
     *
     * @var array|null
     */
    protected $links;

    /**
     * @var array|object|null
     */
    protected $meta;

    /**
     * @var bool
     */
    protected $isAddJsonApiVersion;

    /**
     * @var mixed|null
     */
    protected $jsonApiVersionMeta;

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

        $this->resetEncodeParameters();
    }

    /**
     * @inheritdoc
     */
    public function withLinks(array $links)
    {
        $this->links = array_merge($this->links, $links);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withJsonApiVersion($meta = null)
    {
        $this->isAddJsonApiVersion = true;
        $this->jsonApiVersionMeta  = $meta;

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function withRelationshipSelfLink($resource, $relationshipName, $meta = null, $treatAsHref = false)
    {
        $parentSubLink = $this->container->getSchema($resource)->getSelfSubLink($resource);

        $selfHref = $parentSubLink->getSubHref() .'/'. DocumentInterface::KEYWORD_RELATIONSHIPS .'/'. $relationshipName;
        $links    = [
            DocumentInterface::KEYWORD_SELF => $this->factory->createLink($selfHref, $meta, $treatAsHref),
        ];

        return $this->withLinks($links);
    }

    /**
     * @inheritdoc
     */
    public function withRelationshipRelatedLink($resource, $relationshipName, $meta = null, $treatAsHref = false)
    {
        $parentSubLink = $this->container->getSchema($resource)->getSelfSubLink($resource);

        $selfHref = $parentSubLink->getSubHref() .'/'. $relationshipName;
        $links    = [
            DocumentInterface::KEYWORD_RELATED => $this->factory->createLink($selfHref, $meta, $treatAsHref),
        ];

        return $this->withLinks($links);
    }

    /**
     * @inheritdoc
     */
    public function encodeData($data, EncodingParametersInterface $parameters = null)
    {
        $container = $this->container;
        $result    = $this->encodeDataInternal($container, $data, $parameters);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeIdentifiers($data, EncodingParametersInterface $parameters = null)
    {
        $container = $this->factory->createResourceIdentifierContainerAdapter($this->container);
        $result    = $this->encodeDataInternal($container, $data, $parameters);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeError(ErrorInterface $error)
    {
        $docWriter = $this->factory->createDocument();

        $docWriter->addError($error);

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function encodeErrors($errors)
    {
        $docWriter = $this->factory->createDocument();
        foreach ($errors as $error) {
            $docWriter->addError($error);
        }

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function encodeMeta($meta)
    {
        $docWriter = $this->factory->createDocument();

        $docWriter->setMetaToDocument($meta);
        $docWriter->unsetData();

        return $this->encodeToJson($docWriter->getDocument());
    }

    /**
     * @inheritdoc
     */
    public function encode($data, $links = null, $meta = null, EncodingParametersInterface $parameters = null)
    {
        return $this->withLinks($links === null ? [] : $links)->withMeta($meta)->encodeData($data, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function error(ErrorInterface $error)
    {
        return $this->encodeError($error);
    }

    /**
     * @inheritdoc
     */
    public function errors($errors)
    {
        return $this->encodeErrors($errors);
    }

    /**
     * @inheritdoc
     */
    public function meta($meta)
    {
        return $this->encodeMeta($meta);
    }

    /**
     * @inheritdoc
     */
    public function getEncoderOptions()
    {
        return $this->encoderOptions;
    }

    /**
     * @param ContainerInterface               $container
     * @param object|array|Iterator|null       $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return string
     */
    protected function encodeDataInternal(
        ContainerInterface $container,
        $data,
        EncodingParametersInterface $parameters = null
    ) {
        $dataAnalyzer  = $this->factory->createAnalyzer($container);
        $parameters    = $this->getEncodingParameters($data, $dataAnalyzer, $parameters);
        $docWriter     = $this->factory->createDocument();
        $parserManager = $this->factory->createManager($parameters);
        $interpreter   = $this->factory->createReplyInterpreter($docWriter, $parameters);

        $parser = $this->factory->createParser($dataAnalyzer, $parserManager);

        $this->encoderOptions !== null && $this->encoderOptions->getUrlPrefix() !== null ?
            $docWriter->setUrlPrefix($this->encoderOptions->getUrlPrefix()) : null;

        foreach ($parser->parse($data) as $reply) {
            $interpreter->handle($reply);
        }

        if ($this->meta !== null) {
            $docWriter->setMetaToDocument($this->meta);
        }

        if (empty($this->links) === false) {
            $docWriter->setDocumentLinks($this->links);
        }

        if ($this->isAddJsonApiVersion === true) {
            $docWriter->addJsonApiVersion(self::JSON_API_VERSION, $this->jsonApiVersionMeta);
        } elseif ($this->encoderOptions !== null && $this->encoderOptions->isShowVersionInfo() === true) {
            $docWriter->addJsonApiVersion(self::JSON_API_VERSION, $this->encoderOptions->getVersionMeta());
        }

        $result = $this->encodeToJson($docWriter->getDocument());
        $this->resetEncodeParameters();

        return $result;
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
     * @return EncoderInterface
     */
    public static function instance(array $schemas, EncoderOptions $encodeOptions = null)
    {
        return new static(static::getFactory(), $schemas, $encodeOptions);
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

    /**
     * Reset encode parameters.
     */
    private function resetEncodeParameters()
    {
        $this->meta                = null;
        $this->links               = [];
        $this->isAddJsonApiVersion = false;
        $this->jsonApiVersionMeta  = null;
    }
}
