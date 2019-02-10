<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Parser;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use IteratorAggregate;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Parser\DocumentDataInterface;
use Neomerx\JsonApi\Contracts\Parser\IdentifierInterface;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Parser\PositionInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface as SchemaIdentifierInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;
use Traversable;
use function Neomerx\JsonApi\I18n\format as _;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Parser implements ParserInterface
{
    /** @var string */
    public const MSG_NO_SCHEMA_FOUND = 'No Schema found for top-level resource `%s`.';

    /** @var string */
    public const MSG_NO_DATA_IN_RELATIONSHIP =
        'For resource of type `%s` with ID `%s` relationship `%s` cannot be parsed because it has no data. Skipping.';

    /** @var string */
    public const MSG_CAN_NOT_PARSE_RELATIONSHIP =
        'For resource of type `%s` with ID `%s` relationship `%s` cannot be parsed because it either ' .
        'has `null` or identifier as data. Skipping.';

    /**
     * @var SchemaContainerInterface
     */
    private $schemaContainer;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $paths;

    /**
     * @var array
     */
    private $resourcesTracker;

    /**
     * @param FactoryInterface         $factory
     * @param SchemaContainerInterface $container
     */
    public function __construct(FactoryInterface $factory, SchemaContainerInterface $container)
    {
        $this->resourcesTracker = [];

        $this->setFactory($factory)->setSchemaContainer($container);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function parse($data, array $paths = []): iterable
    {
        \assert(\is_array($data) === true || \is_object($data) === true || $data === null);

        $this->paths = $this->normalizePaths($paths);

        $rootPosition = $this->getFactory()->createPosition(
            ParserInterface::ROOT_LEVEL,
            ParserInterface::ROOT_PATH,
            null,
            null
        );

        if ($this->getSchemaContainer()->hasSchema($data) === true) {
            yield $this->createDocumentDataIsResource($rootPosition);
            yield from $this->parseAsResource($rootPosition, $data);
        } elseif ($data instanceof SchemaIdentifierInterface) {
            yield $this->createDocumentDataIsIdentifier($rootPosition);
            yield $this->parseAsIdentifier($rootPosition, $data);
        } elseif (\is_array($data) === true) {
            yield $this->createDocumentDataIsCollection($rootPosition);
            yield from $this->parseAsResourcesOrIdentifiers($rootPosition, $data);
        } elseif ($data instanceof Traversable) {
            $data = $data instanceof IteratorAggregate ? $data->getIterator() : $data;
            yield $this->createDocumentDataIsCollection($rootPosition);
            yield from $this->parseAsResourcesOrIdentifiers($rootPosition, $data);
        } elseif ($data === null) {
            yield $this->createDocumentDataIsNull($rootPosition);
        } else {
            throw new InvalidArgumentException(_(static::MSG_NO_SCHEMA_FOUND, \get_class($data)));
        }
    }

    /**
     * @return SchemaContainerInterface
     */
    protected function getSchemaContainer(): SchemaContainerInterface
    {
        return $this->schemaContainer;
    }

    /**
     * @param SchemaContainerInterface $container
     *
     * @return self
     */
    protected function setSchemaContainer(SchemaContainerInterface $container): self
    {
        $this->schemaContainer = $container;

        return $this;
    }

    /**
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * @param FactoryInterface $factory
     *
     * @return self
     */
    protected function setFactory(FactoryInterface $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return void
     */
    private function rememberResource(ResourceInterface $resource): void
    {
        $this->resourcesTracker[$resource->getId()][$resource->getType()] = true;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    private function hasSeenResourceBefore(ResourceInterface $resource): bool
    {
        return isset($this->resourcesTracker[$resource->getId()][$resource->getType()]);
    }

    /**
     * @param PositionInterface $position
     * @param iterable          $dataOrIds
     *
     * @see ResourceInterface
     * @see IdentifierInterface
     *
     * @return iterable
     */
    private function parseAsResourcesOrIdentifiers(
        PositionInterface $position,
        iterable $dataOrIds
    ): iterable {
        foreach ($dataOrIds as $dataOrId) {
            if ($this->getSchemaContainer()->hasSchema($dataOrId) === true) {
                yield from $this->parseAsResource($position, $dataOrId);

                continue;
            }

            \assert($dataOrId instanceof SchemaIdentifierInterface);
            yield $this->parseAsIdentifier($position, $dataOrId);
        }
    }

    /**
     * @param PositionInterface $position
     * @param mixed             $data
     *
     * @see ResourceInterface
     *
     * @return iterable
     *
     */
    private function parseAsResource(
        PositionInterface $position,
        $data
    ): iterable {
        \assert($this->getSchemaContainer()->hasSchema($data) === true);

        $resource = $this->getFactory()->createParsedResource(
            $position,
            $this->getSchemaContainer(),
            $data
        );

        yield from $this->parseResource($resource);
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return iterable
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseResource(ResourceInterface $resource): iterable
    {
        $seenBefore = $this->hasSeenResourceBefore($resource);

        // top level resources should be yielded in any case as it could be an array of the resources
        // for deeper levels it's not needed as they go to `included` section and it must have no more
        // than one instance of the same resource.

        if ($resource->getPosition()->getLevel() <= ParserInterface::ROOT_LEVEL || $seenBefore === false) {
            yield $resource;
        }

        // parse relationships only for resources not seen before (prevents infinite loop for circular references)
        if ($seenBefore === false) {
            $this->rememberResource($resource);

            foreach ($resource->getRelationships() as $name => $relationship) {
                \assert(\is_string($name));
                \assert($relationship instanceof RelationshipInterface);

                $isShouldParse = $this->isPathRequested($relationship->getPosition()->getPath());

                if ($relationship->hasData() === true && $isShouldParse === true) {
                    $relData = $relationship->getData();
                    if ($relData->isResource() === true) {
                        yield from $this->parseResource($relData->getResource());

                        continue;
                    } elseif ($relData->isCollection() === true) {
                        foreach ($relData->getResources() as $relResource) {
                            \assert($relResource instanceof ResourceInterface);
                            yield from $this->parseResource($relResource);
                        }

                        continue;
                    }

                    \assert($relData->isNull() || $relData->isIdentifier());
                }
            }
        }
    }

    /**
     * @param PositionInterface         $position
     * @param SchemaIdentifierInterface $identifier
     *
     * @return IdentifierInterface
     */
    private function parseAsIdentifier(
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ): IdentifierInterface {
        return new class ($position, $identifier) implements IdentifierInterface
        {
            /**
             * @var PositionInterface
             */
            private $position;

            /**
             * @var SchemaIdentifierInterface
             */
            private $identifier;

            /**
             * @param PositionInterface         $position
             * @param SchemaIdentifierInterface $identifier
             */
            public function __construct(PositionInterface $position, SchemaIdentifierInterface $identifier)
            {
                $this->position   = $position;
                $this->identifier = $identifier;
            }

            /**
             * @inheritdoc
             */
            public function getPosition(): PositionInterface
            {
                return $this->position;
            }

            /**
             * @inheritdoc
             */
            public function getId(): ?string
            {
                return $this->getIdentifier()->getId();
            }

            /**
             * @inheritdoc
             */
            public function getType(): string
            {
                return $this->getIdentifier()->getType();
            }

            /**
             * @inheritdoc
             */
            public function hasIdentifierMeta(): bool
            {
                return $this->getIdentifier()->hasIdentifierMeta();
            }

            /**
             * @inheritdoc
             */
            public function getIdentifierMeta()
            {
                return $this->getIdentifier()->getIdentifierMeta();
            }

            /**
             * @return SchemaIdentifierInterface
             */
            private function getIdentifier(): SchemaIdentifierInterface
            {
                return $this->identifier;
            }
        };
    }

    /**
     * @param PositionInterface $position
     *
     * @return DocumentDataInterface
     */
    private function createDocumentDataIsCollection(PositionInterface $position): DocumentDataInterface
    {
        return $this->createParsedDocumentData($position, true, false);
    }

    /**
     * @param PositionInterface $position
     *
     * @return DocumentDataInterface
     */
    private function createDocumentDataIsNull(PositionInterface $position): DocumentDataInterface
    {
        return $this->createParsedDocumentData($position, false, true);
    }

    /**
     * @param PositionInterface $position
     *
     * @return DocumentDataInterface
     */
    private function createDocumentDataIsResource(PositionInterface $position): DocumentDataInterface
    {
        return $this->createParsedDocumentData($position, false, false);
    }

    /**
     * @param PositionInterface $position
     *
     * @return DocumentDataInterface
     */
    private function createDocumentDataIsIdentifier(PositionInterface $position): DocumentDataInterface
    {
        return $this->createParsedDocumentData($position, false, false);
    }

    /**
     * @param PositionInterface $position
     * @param bool              $isCollection
     * @param bool              $isNull
     *
     * @return DocumentDataInterface
     */
    private function createParsedDocumentData(
        PositionInterface $position,
        bool $isCollection,
        bool $isNull
    ): DocumentDataInterface {
        return new class (
            $position,
            $isCollection,
            $isNull
        ) implements DocumentDataInterface
        {
            /**
             * @var PositionInterface
             */
            private $position;
            /**
             * @var bool
             */
            private $isCollection;

            /**
             * @var bool
             */
            private $isNull;

            /**
             * @param PositionInterface $position
             * @param bool              $isCollection
             * @param bool              $isNull
             */
            public function __construct(
                PositionInterface $position,
                bool $isCollection,
                bool $isNull
            ) {
                $this->position     = $position;
                $this->isCollection = $isCollection;
                $this->isNull       = $isNull;
            }

            /**
             * @inheritdoc
             */
            public function getPosition(): PositionInterface
            {
                return $this->position;
            }

            /**
             * @inheritdoc
             */
            public function isCollection(): bool
            {
                return $this->isCollection;
            }

            /**
             * @inheritdoc
             */
            public function isNull(): bool
            {
                return $this->isNull;
            }
        };
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isPathRequested(string $path): bool
    {
        return \array_key_exists($path, $this->paths);
    }

    /**
     * @param iterable $paths
     *
     * @return array
     */
    private function normalizePaths(iterable $paths): array
    {
        $separator = DocumentInterface::PATH_SEPARATOR;

        // convert paths like a.b.c to paths that actually should be used a, a.b, a.b.c
        $normalizedPaths = [];
        foreach ($paths as $path) {
            $curPath = '';
            foreach (\explode($separator, $path) as $pathPart) {
                $curPath                   = empty($curPath) === true ? $pathPart : $curPath . $separator . $pathPart;
                $normalizedPaths[$curPath] = true;
            }
        }

        return $normalizedPaths;
    }
}
