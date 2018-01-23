<?php namespace Neomerx\JsonApi\Encoder;

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

use InvalidArgumentException;
use Iterator;
use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Neomerx\JsonApi\Factories\Factory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

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
    public function withLinks(array $links): EncoderInterface
    {
        $this->links = array_merge($this->links, $links);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withMeta($meta): EncoderInterface
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withJsonApiVersion($version = null): EncoderInterface
    {
        $this->isAddJsonApiVersion = $version !== null;
        $this->jsonApiVersionMeta  = $version;

        return $this;
    }


    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function withRelationshipSelfLink(
        $resource,
        string $relationshipName,
        $meta = null,
        bool $treatAsHref = false
    ): EncoderInterface {
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
    public function withRelationshipRelatedLink(
        $resource,
        string $relationshipName,
        $meta = null,
        bool $treatAsHref = false
    ): EncoderInterface {
        $link = $this->getContainer()->getSchema($resource)
            ->getRelationshipRelatedLink($resource, $relationshipName, $meta, $treatAsHref);

        return $this->withLinks([
            DocumentInterface::KEYWORD_RELATED => $link,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function encodeData($data, EncodingParametersInterface $parameters = null): string
    {
        $array  = $this->encodeDataToArray($this->getContainer(), $data, $parameters);
        $result = $this->encodeToJson($array);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeIdentifiers($data, EncodingParametersInterface $parameters = null): string
    {
        $array  = $this->encodeIdentifiersToArray($data, $parameters);
        $result = $this->encodeToJson($array);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function encodeError(ErrorInterface $error): string
    {
        return $this->encodeToJson($this->encodeErrorToArray($error));
    }

    /**
     * @inheritdoc
     */
    public function encodeErrors($errors): string
    {
        return $this->encodeToJson($this->encodeErrorsToArray($errors));
    }

    /**
     * @inheritdoc
     */
    public function encodeMeta($meta): string
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
    ): array {
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
    protected function encodeToJson(array $document): string
    {
        return $this->getEncoderOptions() === null ?
            json_encode($document) :
            json_encode($document, $this->getEncoderOptions()->getOptions(), $this->getEncoderOptions()->getDepth());
    }

    /**
     * Create encoder instance.
     *
     * @param array               $schemas Schema providers.
     * @param EncoderOptions|null $encodeOptions
     *
     * @return EncoderInterface
     */
    public static function instance(array $schemas = [], EncoderOptions $encodeOptions = null): EncoderInterface
    {
        $factory   = static::createFactory();
        $container = $factory->createContainer($schemas);
        $encoder   = $factory->createEncoder($container, $encodeOptions);

        return $encoder;
    }

    /**
     * @return FactoryInterface
     */
    protected static function createFactory(): FactoryInterface
    {
        return new Factory();
    }

    /**
     * @param mixed $data
     */
    protected function checkInputData($data): void
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
    protected function encodeIdentifiersToArray($data, EncodingParametersInterface $parameters = null): array
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
    protected function encodeErrorToArray(ErrorInterface $error): array
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
    protected function encodeErrorsToArray($errors): array
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
    protected function encodeMetaToArray($meta): array
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
    protected function addTopLevelMeta(DocumentInterface $docWriter): void
    {
        if ($this->getMeta() !== null) {
            $docWriter->setMetaToDocument($this->getMeta());
        }
    }

    /**
     * @param DocumentInterface $docWriter
     */
    protected function addTopLevelLinks(DocumentInterface $docWriter): void
    {
        if (empty($this->getLinks()) === false) {
            $docWriter->setDocumentLinks($this->getLinks());
        }
    }

    /**
     * @param DocumentInterface $docWriter
     */
    protected function addTopLevelJsonApiVersion(DocumentInterface $docWriter): void
    {
        if ($this->isWithJsonApiVersion() === true) {
            $docWriter->addJsonApiVersion(self::JSON_API_VERSION, $this->getJsonApiVersionMeta());
        }
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * @return EncoderOptions|null
     */
    protected function getEncoderOptions(): ?EncoderOptions
    {
        return $this->encoderOptions;
    }

    /**
     * @return array|null
     */
    protected function getLinks(): ?array
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
     * @return bool
     */
    protected function isWithJsonApiVersion(): bool
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
    ): ParametersAnalyzerInterface {
        return $this->getFactory()->createParametersAnalyzer(
            $parameters === null ? $this->getFactory()->createQueryParameters() : $parameters,
            $container
        );
    }

    /**
     * Reset encode parameters.
     */
    private function resetEncodeParameters(): void
    {
        $this->meta                = null;
        $this->links               = [];
        $this->isAddJsonApiVersion = false;
        $this->jsonApiVersionMeta  = null;
    }

    /**
     * @param DocumentInterface $docWriter
     */
    private function configureUrlPrefix(DocumentInterface $docWriter): void
    {
        $this->getEncoderOptions() !== null && $this->getEncoderOptions()->getUrlPrefix() !== null ?
            $docWriter->setUrlPrefix($this->getEncoderOptions()->getUrlPrefix()) : null;
    }
}
