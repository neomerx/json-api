<?php namespace Neomerx\JsonApi\Encoder;

/**
 * Copyright 2015-2017 info@neomerx.com
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
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Encoder implements EncoderInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var EncoderOptions|null
     */
    private $encoderOptions;

    /**
     * Links in array<string,LinkInterface> format.
     *
     * @var array|null
     */
    private $links;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @var bool
     */
    private $isAddJsonApiVersion;

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
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function withRelationshipSelfLink($resource, $relationshipName, $meta = null, $treatAsHref = false)
    {
        $link = $this->getContainer()->getSchema($resource)
            ->getRelationshipSelfLink($resource, $relationshipName, $meta, $treatAsHref);

        return $this->withLinks([
            DocumentInterface::KEYWORD_SELF => $link,
        ]);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function withRelationshipRelatedLink($resource, $relationshipName, $meta = null, $treatAsHref = false)
    {
        $link = $this->getContainer()->getSchema($resource)
            ->getRelationshipRelatedLink($resource, $relationshipName, $meta, $treatAsHref);

        return $this->withLinks([
            DocumentInterface::KEYWORD_RELATED => $link,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function encodeData($data, EncodingParametersInterface $parameters = null)
    {
        $array  = $this->encodeDataToArray($this->getContainer(), $data, $parameters);
        $result = $this->encodeToJson($array);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeIdentifiers($data, EncodingParametersInterface $parameters = null)
    {
        $array  = $this->encodeIdentifiersToArray($data, $parameters);
        $result = $this->encodeToJson($array);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeError(ErrorInterface $error)
    {
        return $this->encodeToJson($this->encodeErrorToArray($error));
    }

    /**
     * @inheritdoc
     */
    public function encodeErrors($errors)
    {
        return $this->encodeToJson($this->encodeErrorsToArray($errors));
    }

    /**
     * @inheritdoc
     */
    public function encodeMeta($meta)
    {
        return $this->encodeToJson($this->encodeMetaToArray($meta));
    }

    /**
     * @param ContainerInterface               $container
     * @param object|array|Iterator|null       $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return array
     */
    protected function encodeDataToArray(
        ContainerInterface $container,
        $data,
        EncodingParametersInterface $parameters = null
    ) {
        $this->checkInputData($data);

        $docWriter     = $this->getFactory()->createDocument();
        $paramAnalyzer = $this->createParametersAnalyzer($container, $parameters);
        $parserManager = $this->getFactory()->createManager($paramAnalyzer);
        $interpreter   = $this->getFactory()->createReplyInterpreter($docWriter, $paramAnalyzer);
        $parser        = $this->getFactory()->createParser($container, $parserManager);

        $this->configureUrlPrefix($docWriter);

        foreach ($parser->parse($data) as $reply) {
            $interpreter->handle($reply);
        }

        $this->addTopLevelMeta($docWriter);
        $this->addTopLevelLinks($docWriter);
        $this->addTopLevelJsonApiVersion($docWriter);

        $result = $docWriter->getDocument();
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
        return $this->getEncoderOptions() === null ?
            json_encode($document) :
            json_encode($document, $this->getEncoderOptions()->getOptions(), $this->getEncoderOptions()->getDepth());
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
        $factory   = static::createFactory();
        $container = $factory->createContainer($schemas);
        $encoder   = $factory->createEncoder($container, $encodeOptions);

        return $encoder;
    }

    /**
     * @return FactoryInterface
     */
    protected static function createFactory()
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
     * @param object|array|Iterator|null       $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return array
     */
    protected function encodeIdentifiersToArray($data, EncodingParametersInterface $parameters = null)
    {
        $container = $this->getFactory()->createResourceIdentifierContainerAdapter($this->getContainer());
        $result    = $this->encodeDataToArray($container, $data, $parameters);

        return $result;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return array
     */
    protected function encodeErrorToArray(ErrorInterface $error)
    {
        $docWriter = $this->getFactory()->createDocument();
        $docWriter->addError($error);

        $this->addTopLevelMeta($docWriter);
        $this->addTopLevelJsonApiVersion($docWriter);

        $array = $docWriter->getDocument();

        return $array;
    }

    /**
     * @param $errors
     *
     * @return array
     */
    protected function encodeErrorsToArray($errors)
    {
        $docWriter = $this->getFactory()->createDocument();
        $docWriter->addErrors($errors);

        $this->addTopLevelMeta($docWriter);
        $this->addTopLevelJsonApiVersion($docWriter);

        $array = $docWriter->getDocument();

        return $array;
    }

    /**
     * @param $meta
     *
     * @return array
     */
    protected function encodeMetaToArray($meta)
    {
        $docWriter = $this->getFactory()->createDocument();

        $docWriter->setMetaToDocument($meta);
        $docWriter->unsetData();
        $array = $docWriter->getDocument();

        return $array;
    }

    /**
     * @param DocumentInterface $docWriter
     */
    protected function addTopLevelMeta(DocumentInterface $docWriter)
    {
        if ($this->getMeta() !== null) {
            $docWriter->setMetaToDocument($this->getMeta());
        }
    }

    /**
     * @param DocumentInterface $docWriter
     */
    protected function addTopLevelLinks(DocumentInterface $docWriter)
    {
        if (empty($this->getLinks()) === false) {
            $docWriter->setDocumentLinks($this->getLinks());
        }
    }

    /**
     * @param DocumentInterface $docWriter
     */
    protected function addTopLevelJsonApiVersion(DocumentInterface $docWriter)
    {
        if ($this->isWithJsonApiVersion() === true) {
            $docWriter->addJsonApiVersion(self::JSON_API_VERSION, $this->getJsonApiVersionMeta());
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return FactoryInterface
     */
    protected function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return EncoderOptions|null
     */
    protected function getEncoderOptions()
    {
        return $this->encoderOptions;
    }

    /**
     * @return array|null
     */
    protected function getLinks()
    {
        return $this->links;
    }

    /**
     * @return array|null|object
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @return boolean
     */
    protected function isWithJsonApiVersion()
    {
        return $this->isAddJsonApiVersion;
    }

    /**
     * @return mixed|null
     */
    protected function getJsonApiVersionMeta()
    {
        return $this->jsonApiVersionMeta;
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
        return $this->getFactory()->createParametersAnalyzer(
            $parameters === null ? $this->getFactory()->createQueryParameters() : $parameters,
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
        $this->getEncoderOptions() !== null && $this->getEncoderOptions()->getUrlPrefix() !== null ?
            $docWriter->setUrlPrefix($this->getEncoderOptions()->getUrlPrefix()) : null;
    }
}
