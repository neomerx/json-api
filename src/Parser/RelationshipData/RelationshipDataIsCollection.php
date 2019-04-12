<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Parser\RelationshipData;

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
use Neomerx\JsonApi\Contracts\Parser\IdentifierInterface as ParserIdentifierInterface;
use Neomerx\JsonApi\Contracts\Parser\PositionInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipDataInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Schema\IdentifierInterface as SchemaIdentifierInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Exceptions\LogicException;
use function Neomerx\JsonApi\I18n\format as _;

/**
 * @package Neomerx\JsonApi
 */
class RelationshipDataIsCollection extends BaseRelationshipData implements RelationshipDataInterface
{
    /** @var string */
    public const MSG_INVALID_OPERATION = 'Invalid operation.';

    /**
     * @var iterable
     */
    private $resources;

    /**
     * @var iterable
     */
    private $parsedResources = null;

    /**
     * @param FactoryInterface         $factory
     * @param SchemaContainerInterface $schemaContainer
     * @param PositionInterface        $position
     * @param iterable                 $resources
     */
    public function __construct(
        FactoryInterface $factory,
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        iterable $resources
    ) {
        parent::__construct($factory, $schemaContainer, $position);

        $this->resources = $resources;
    }

    /**
     * @inheritdoc
     */
    public function isCollection(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isNull(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isResource(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isIdentifier(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ParserIdentifierInterface
    {
        throw new LogicException(_(static::MSG_INVALID_OPERATION));
    }

    /**
     * @inheritdoc
     */
    public function getIdentifiers(): iterable
    {
        return $this->getResources();
    }

    /**
     * @inheritdoc
     */
    public function getResource(): ResourceInterface
    {
        throw new LogicException(_(static::MSG_INVALID_OPERATION));
    }

    /**
     * @inheritdoc
     */
    public function getResources(): iterable
    {
        if ($this->parsedResources === null) {
            foreach ($this->resources as $resourceOrIdentifier) {
                $parsedResource          = $resourceOrIdentifier instanceof SchemaIdentifierInterface ?
                    $this->createParsedIdentifier($resourceOrIdentifier) :
                    $this->createParsedResource($resourceOrIdentifier);
                $this->parsedResources[] = $parsedResource;

                yield $parsedResource;
            }

            return;
        }

        yield from $this->parsedResources;
    }
}
