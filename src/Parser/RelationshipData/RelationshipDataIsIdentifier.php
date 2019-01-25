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
class RelationshipDataIsIdentifier extends BaseRelationshipData implements RelationshipDataInterface
{
    /** @var string */
    public const MSG_INVALID_OPERATION = 'Invalid operation.';

    /**
     * @var mixed
     */
    private $identifier;

    /**
     * @var null|ParserIdentifierInterface
     */
    private $parsedIdentifier = null;

    /**
     * @param FactoryInterface          $factory
     * @param SchemaContainerInterface  $schemaContainer
     * @param PositionInterface         $position
     * @param SchemaIdentifierInterface $identifier
     */
    public function __construct(
        FactoryInterface $factory,
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ) {
        parent::__construct($factory, $schemaContainer, $position);

        $this->identifier = $identifier;
    }

    /**
     * @inheritdoc
     */
    public function isCollection(): bool
    {
        return false;
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
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifier(): ParserIdentifierInterface
    {
        if ($this->parsedIdentifier === null) {
            $this->parsedIdentifier = $this->createParsedIdentifier($this->identifier);
        }

        return $this->parsedIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifiers(): iterable
    {
        throw new LogicException(_(static::MSG_INVALID_OPERATION));
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
        throw new LogicException(_(static::MSG_INVALID_OPERATION));
    }
}
