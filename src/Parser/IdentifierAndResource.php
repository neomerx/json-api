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

use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Parser\ParserInterface;
use Neomerx\JsonApi\Contracts\Parser\PositionInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Parser\RelationshipData\ParseRelationshipDataTrait;
use Neomerx\JsonApi\Parser\RelationshipData\ParseRelationshipLinksTrait;

/**
 * @package Neomerx\JsonApi
 */
class IdentifierAndResource implements ResourceInterface
{
    use ParseRelationshipDataTrait, ParseRelationshipLinksTrait;

    /** @var string */
    public const MSG_NO_SCHEMA_FOUND = 'No Schema found for resource `%s` at path `%s`.';

    /** @var string */
    public const MSG_INVALID_OPERATION = 'Invalid operation.';

    /**
     * @var PositionInterface
     */
    private $position;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var SchemaContainerInterface
     */
    private $schemaContainer;

    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @var null|array
     */
    private $links = null;

    /**
     * @var null|array
     */
    private $relationshipsCache = null;

    /**
     * @param PositionInterface        $position
     * @param FactoryInterface         $factory
     * @param SchemaContainerInterface $container
     * @param mixed                    $data
     */
    public function __construct(
        PositionInterface $position,
        FactoryInterface $factory,
        SchemaContainerInterface $container,
        $data
    ) {
        \assert($position->getLevel() >= ParserInterface::ROOT_LEVEL);

        $schema = $container->getSchema($data);

        $this->position        = $position;
        $this->factory         = $factory;
        $this->schemaContainer = $container;
        $this->schema          = $schema;
        $this->data            = $data;
        $this->index           = $schema->getId($data);
        $this->type            = $schema->getType();
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
        return $this->index;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function hasIdentifierMeta(): bool
    {
        return $this->schema->hasIdentifierMeta($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getIdentifierMeta()
    {
        return $this->schema->getIdentifierMeta($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(): iterable
    {
        return $this->schema->getAttributes($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getRelationships(): iterable
    {
        if ($this->relationshipsCache !== null) {
            yield from $this->relationshipsCache;

            return;
        }

        $this->relationshipsCache = [];

        $currentPath    = $this->position->getPath();
        $nextLevel      = $this->position->getLevel() + 1;
        $nextPathPrefix = empty($currentPath) === true ? '' : $currentPath . PositionInterface::PATH_SEPARATOR;
        foreach ($this->schema->getRelationships($this->data) as $name => $description) {
            \assert($this->assertRelationshipNameAndDescription($name, $description) === true);

            [$hasData, $relationshipData, $nextPosition] = $this->parseRelationshipData(
                $this->factory,
                $this->schemaContainer,
                $this->type,
                $name,
                $description,
                $nextLevel,
                $nextPathPrefix
            );

            [$hasLinks, $links] =
                $this->parseRelationshipLinks($this->schema, $this->data, $name, $description);

            $hasMeta = \array_key_exists(SchemaInterface::RELATIONSHIP_META, $description);
            $meta    = $hasMeta === true ? $description[SchemaInterface::RELATIONSHIP_META] : null;

            \assert(
                $hasData || $hasMeta || $hasLinks,
                "Relationship `$name` for type `" . $this->getType() .
                '` MUST contain at least one of the following: links, data or meta.'
            );

            $relationship = $this->factory->createRelationship(
                $nextPosition,
                $hasData,
                $relationshipData,
                $hasLinks,
                $links,
                $hasMeta,
                $meta
            );

            $this->relationshipsCache[$name] = $relationship;

            yield $name => $relationship;
        }
    }

    /**
     * @inheritdoc
     */
    public function hasLinks(): bool
    {
        $this->cacheLinks();

        return empty($this->links) === false;
    }

    /**
     * @inheritdoc
     */
    public function getLinks(): iterable
    {
        $this->cacheLinks();

        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta(): bool
    {
        return $this->schema->hasResourceMeta($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getResourceMeta()
    {
        return $this->schema->getResourceMeta($this->data);
    }

    /**
     * Read and parse links from schema.
     */
    private function cacheLinks(): void
    {
        if ($this->links === null) {
            $this->links = [];
            foreach ($this->schema->getLinks($this->data) as $name => $link) {
                \assert(\is_string($name) === true && empty($name) === false);
                \assert($link instanceof LinkInterface);
                $this->links[$name] = $link;
            }
        }
    }

    /**
     * @param string $name
     * @param array  $description
     *
     * @return bool
     */
    private function assertRelationshipNameAndDescription(string $name, array $description): bool
    {
        \assert(
            \is_string($name) === true && empty($name) === false,
            "Relationship names for type `" . $this->getType() . '` should be non-empty strings.'
        );
        \assert(
            \is_array($description) === true && empty($description) === false,
            "Relationship `$name` for type `" . $this->getType() . '` should be a non-empty array.'
        );

        return true;
    }
}
