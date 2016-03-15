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
use \InvalidArgumentException;
use \Psr\Log\LoggerAwareTrait;
use \Psr\Log\LoggerAwareInterface;
use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;

/**
 * @package Neomerx\JsonApi
 */
class Encoder implements EncoderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

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
     * @param ContainerInterface  $container
     * @param EncoderOptions|null $encoderOptions
     */
    public function __construct(
        FactoryInterface $factory,
        ContainerInterface $container,
        EncoderOptions $encoderOptions = null
    ) {
        $this->factory        = $factory;
        $this->container      = $container;
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
        return $this->getRelationshipLink(
            $resource,
            DocumentInterface::KEYWORD_SELF,
            '/' . DocumentInterface::KEYWORD_RELATIONSHIPS,
            $relationshipName,
            $meta,
            $treatAsHref
        );
    }

    /**
     * @inheritdoc
     */
    public function withRelationshipRelatedLink($resource, $relationshipName, $meta = null, $treatAsHref = false)
    {
        return $this->getRelationshipLink(
            $resource,
            DocumentInterface::KEYWORD_RELATED,
            '',
            $relationshipName,
            $meta,
            $treatAsHref
        );
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
        $this->checkInputData($data);

        $docWriter     = $this->factory->createDocument();
        $paramAnalyzer = $this->createParametersAnalyzer($container, $parameters);
        $parserManager = $this->factory->createManager($paramAnalyzer);
        $interpreter   = $this->factory->createReplyInterpreter($docWriter, $paramAnalyzer);
        $parser        = $this->factory->createParser($container, $parserManager);

        $this->configureUrlPrefix($docWriter);

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
    public static function instance(array $schemas = [], EncoderOptions $encodeOptions = null)
    {
        $factory   = static::getFactory();
        $container = $factory->createContainer($schemas);
        $encoder   = $factory->createEncoder($container, $encodeOptions);

        return $encoder;
    }

    /**
     * @return FactoryInterface
     */
    protected static function getFactory()
    {
        return new Factory();
    }

    /**
     * @param mixed $data
     */
    protected function checkInputData($data)
    {
        if (is_array($data) === false && is_object($data) === false && $data !== null && !($data instanceof Iterator)) {
            throw new InvalidArgumentException('data');
        }
    }

    /**
     * @param ContainerInterface               $container
     * @param EncodingParametersInterface|null $parameters
     *
     * @return ParametersAnalyzerInterface
     */
    private function createParametersAnalyzer(
        ContainerInterface $container,
        EncodingParametersInterface $parameters = null
    ) {
        return $this->factory->createParametersAnalyzer(
            $parameters === null ? $this->factory->createEncodingParameters() : $parameters,
            $container
        );
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

    /**
     * @param DocumentInterface $docWriter
     */
    private function configureUrlPrefix(DocumentInterface $docWriter)
    {
        $this->encoderOptions !== null && $this->encoderOptions->getUrlPrefix() !== null ?
            $docWriter->setUrlPrefix($this->encoderOptions->getUrlPrefix()) : null;
    }

    /**
     * @param object     $resource
     * @param string     $key
     * @param string     $prefix
     * @param string     $name
     * @param null|mixed $meta
     * @param bool       $treatAsHref
     *
     * @return EncoderInterface
     */
    private function getRelationshipLink($resource, $key, $prefix, $name, $meta = null, $treatAsHref = false)
    {
        $parentSubLink = $this->container->getSchema($resource)->getSelfSubLink($resource);

        $selfHref = $parentSubLink->getSubHref() . $prefix .'/'. $name;
        $links    = [
            $key => $this->factory->createLink($selfHref, $meta, $treatAsHref),
        ];

        return $this->withLinks($links);
    }
}
