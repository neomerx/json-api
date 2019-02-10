<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Factories;

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

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Parser\IdentifierInterface as ParserIdentifierInterface;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Parser\PositionInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipDataInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Representation\DocumentWriterInterface;
use Neomerx\JsonApi\Contracts\Representation\ErrorWriterInterface;
use Neomerx\JsonApi\Contracts\Representation\FieldSetFilterInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface as SchemaIdentifierInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Http\Headers\AcceptMediaType;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Neomerx\JsonApi\Parser\IdentifierAndResource;
use Neomerx\JsonApi\Parser\Parser;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsCollection;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsIdentifier;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsNull;
use Neomerx\JsonApi\Parser\RelationshipData\RelationshipDataIsResource;
use Neomerx\JsonApi\Representation\DocumentWriter;
use Neomerx\JsonApi\Representation\ErrorWriter;
use Neomerx\JsonApi\Representation\FieldSetFilter;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Schema\SchemaContainer;

/**
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Factory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface
    {
        return new Encoder($this, $container);
    }

    /**
     * @inheritdoc
     */
    public function createSchemaContainer(iterable $schemas): SchemaContainerInterface
    {
        return new SchemaContainer($this, $schemas);
    }

    /**
     * @inheritdoc
     */
    public function createPosition(
        int $level,
        string $path,
        ?string $parentType,
        ?string $parentRelationship
    ): PositionInterface {
        return new class ($level, $path, $parentType, $parentRelationship) implements PositionInterface
        {
            /**
             * @var int
             */
            private $level;

            /**
             * @var string
             */
            private $path;

            /**
             * @var null|string
             */
            private $parentType;

            /**
             * @var null|string
             */
            private $parentRelationship;

            /**
             * @param int         $level
             * @param string      $path
             * @param null|string $parentType
             * @param null|string $parentRelationship
             */
            public function __construct(int $level, string $path, ?string $parentType, ?string $parentRelationship)
            {
                $this->level              = $level;
                $this->path               = $path;
                $this->parentType         = $parentType;
                $this->parentRelationship = $parentRelationship;
            }

            /**
             * @inheritdoc
             */
            public function getLevel(): int
            {
                return $this->level;
            }

            /**
             * @inheritdoc
             */
            public function getPath(): string
            {
                return $this->path;
            }

            /**
             * @inheritdoc
             */
            public function getParentType(): ?string
            {
                return $this->parentType;
            }

            /**
             * @inheritdoc
             */
            public function getParentRelationship(): ?string
            {
                return $this->parentRelationship;
            }
        };
    }

    /**
     * @inheritdoc
     */
    public function createParser(SchemaContainerInterface $container): ParserInterface
    {
        return new Parser($this, $container);
    }

    /**
     * @inheritdoc
     */
    public function createDocumentWriter(): DocumentWriterInterface
    {
        return new DocumentWriter();
    }

    /**
     * @inheritdoc
     */
    public function createErrorWriter(): ErrorWriterInterface
    {
        return new ErrorWriter();
    }

    /**
     * @inheritdoc
     */
    public function createFieldSetFilter(array $fieldSets): FieldSetFilterInterface
    {
        return new FieldSetFilter($fieldSets);
    }

    /**
     * @inheritdoc
     */
    public function createParsedResource(
        PositionInterface $position,
        SchemaContainerInterface $container,
        $data
    ): ResourceInterface {
        return new IdentifierAndResource($position, $this, $container, $data);
    }

    /**
     * @inheritdoc
     */
    public function createParsedIdentifier(
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ): ParserIdentifierInterface {
        return new class ($position, $identifier) implements ParserIdentifierInterface
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
            public function __construct(
                PositionInterface $position,
                SchemaIdentifierInterface $identifier
            ) {
                $this->position   = $position;
                $this->identifier = $identifier;

                // for test coverage only
                \assert($this->getPosition() !== null);
            }

            /**
             * @inheritdoc
             */
            public function getType(): string
            {
                return $this->identifier->getType();
            }

            /**
             * @inheritdoc
             */
            public function getId(): ?string
            {
                return $this->identifier->getId();
            }

            /**
             * @inheritdoc
             */
            public function hasIdentifierMeta(): bool
            {
                return $this->identifier->hasIdentifierMeta();
            }

            /**
             * @inheritdoc
             */
            public function getIdentifierMeta()
            {
                return $this->identifier->getIdentifierMeta();
            }

            /**
             * @inheritdoc
             */
            public function getPosition(): PositionInterface
            {
                return $this->position;
            }
        };
    }

    /**
     * @inheritdoc
     */
    public function createLink(bool $isSubUrl, string $value, bool $hasMeta, $meta = null): LinkInterface
    {
        return new Link($isSubUrl, $value, $hasMeta, $meta);
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function createRelationship(
        PositionInterface $position,
        bool $hasData,
        ?RelationshipDataInterface $data,
        bool $hasLinks,
        ?iterable $links,
        bool $hasMeta,
        $meta
    ): RelationshipInterface {
        return new class (
            $position,
            $hasData,
            $data,
            $hasLinks,
            $links,
            $hasMeta,
            $meta
        ) implements RelationshipInterface
        {
            /**
             * @var PositionInterface
             */
            private $position;

            /**
             * @var bool
             */
            private $hasData;

            /**
             * @var ?RelationshipDataInterface
             */
            private $data;

            /**
             * @var bool
             */
            private $hasLinks;

            /**
             * @var ?iterable
             */
            private $links;

            /**
             * @var bool
             */
            private $hasMeta;

            /**
             * @var mixed
             */
            private $meta;

            /**
             * @var bool
             */
            private $metaIsCallable;

            /**
             * @param PositionInterface              $position
             * @param bool                           $hasData
             * @param RelationshipDataInterface|null $data
             * @param bool                           $hasLinks
             * @param iterable|null                  $links
             * @param bool                           $hasMeta
             * @param mixed                          $meta
             */
            public function __construct(
                PositionInterface $position,
                bool $hasData,
                ?RelationshipDataInterface $data,
                bool $hasLinks,
                ?iterable $links,
                bool $hasMeta,
                $meta
            ) {
                \assert($position->getLevel() > ParserInterface::ROOT_LEVEL);
                \assert(empty($position->getPath()) === false);
                \assert(($hasData === false && $data === null) || ($hasData === true && $data !== null));
                \assert(($hasLinks === false && $links === null) || ($hasLinks === true && $links !== null));

                $this->position       = $position;
                $this->hasData        = $hasData;
                $this->data           = $data;
                $this->hasLinks       = $hasLinks;
                $this->links          = $links;
                $this->hasMeta        = $hasMeta;
                $this->meta           = $meta;
                $this->metaIsCallable = \is_callable($meta);
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
            public function hasData(): bool
            {
                return $this->hasData;
            }

            /**
             * @inheritdoc
             */
            public function getData(): RelationshipDataInterface
            {
                \assert($this->hasData());

                return $this->data;
            }

            /**
             * @inheritdoc
             */
            public function hasLinks(): bool
            {
                return $this->hasLinks;
            }

            /**
             * @inheritdoc
             */
            public function getLinks(): iterable
            {
                \assert($this->hasLinks());

                return $this->links;
            }

            /**
             * @inheritdoc
             */
            public function hasMeta(): bool
            {
                return $this->hasMeta;
            }

            /**
             * @inheritdoc
             */
            public function getMeta()
            {
                \assert($this->hasMeta());

                if ($this->metaIsCallable === true) {
                    $this->meta           = \call_user_func($this->meta);
                    $this->metaIsCallable = false;
                }

                return $this->meta;
            }
        };
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipDataIsResource(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        $resource
    ): RelationshipDataInterface {
        return new RelationshipDataIsResource($this, $schemaContainer, $position, $resource);
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipDataIsIdentifier(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ): RelationshipDataInterface {
        return new RelationshipDataIsIdentifier($this, $schemaContainer, $position, $identifier);
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipDataIsCollection(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        iterable $resources
    ): RelationshipDataInterface {
        return new RelationshipDataIsCollection($this, $schemaContainer, $position, $resources);
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipDataIsNull(): RelationshipDataInterface
    {
        return new RelationshipDataIsNull();
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
    public function createAcceptMediaType(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0
    ): AcceptMediaTypeInterface {
        return new AcceptMediaType($position, $type, $subType, $parameters, $quality);
    }
}
