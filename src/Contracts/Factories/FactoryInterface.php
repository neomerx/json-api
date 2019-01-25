<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Factories;

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

/**
 * @package Neomerx\JsonApi
 */
interface FactoryInterface
{
    /**
     * Create encoder.
     *
     * @param SchemaContainerInterface $container
     *
     * @return EncoderInterface
     */
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface;

    /**
     * Create Schema container.
     *
     * @param iterable $schemas
     *
     * @return SchemaContainerInterface
     */
    public function createSchemaContainer(iterable $schemas): SchemaContainerInterface;

    /**
     * Create resources parser.
     *
     * @param SchemaContainerInterface $container
     *
     * @return ParserInterface
     */
    public function createParser(SchemaContainerInterface $container): ParserInterface;

    /**
     * Create position for a parsed result.
     *
     * @param int         $level
     * @param string      $path
     * @param null|string $parentType
     * @param null|string $parentRelationship
     *
     * @return PositionInterface
     */
    public function createPosition(
        int $level,
        string $path,
        ?string $parentType,
        ?string $parentRelationship
    ): PositionInterface;

    /**
     * Create JSON API document writer.
     *
     * @return DocumentWriterInterface
     */
    public function createDocumentWriter(): DocumentWriterInterface;

    /**
     * Create JSON API error writer.
     *
     * @return ErrorWriterInterface
     */
    public function createErrorWriter(): ErrorWriterInterface;

    /**
     * Create filter for attributes and relationships.
     *
     * @param array $fieldSets
     *
     * @return FieldSetFilterInterface
     */
    public function createFieldSetFilter(array $fieldSets): FieldSetFilterInterface;

    /**
     * Create parsed resource over raw resource data.
     *
     * @param PositionInterface        $position
     * @param SchemaContainerInterface $container
     * @param mixed                    $data
     *
     * @return ResourceInterface
     */
    public function createParsedResource(
        PositionInterface $position,
        SchemaContainerInterface $container,
        $data
    ): ResourceInterface;

    /**
     * Create parsed identifier over raw resource identifier.
     *
     * @param PositionInterface         $position
     * @param SchemaIdentifierInterface $identifier
     *
     * @return ParserIdentifierInterface
     */
    public function createParsedIdentifier(
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ): ParserIdentifierInterface;

    /**
     * Create link.
     *
     * @param bool   $isSubUrl If value is either full URL or sub-URL.
     * @param string $value    Either full URL or sub-URL.
     * @param bool   $hasMeta  If links has meta information.
     * @param null   $meta     Value for meta.
     *
     * @return LinkInterface
     */
    public function createLink(bool $isSubUrl, string $value, bool $hasMeta, $meta = null): LinkInterface;

    /**
     * Create parsed relationship.
     *
     * @param PositionInterface              $position
     * @param bool                           $hasData
     * @param RelationshipDataInterface|null $data
     * @param bool                           $hasLinks
     * @param iterable|null                  $links
     * @param bool                           $hasMeta
     * @param mixed                          $meta
     *
     * @return RelationshipInterface
     */
    public function createRelationship(
        PositionInterface $position,
        bool $hasData,
        ?RelationshipDataInterface $data,
        bool $hasLinks,
        ?iterable $links,
        bool $hasMeta,
        $meta
    ): RelationshipInterface;

    /**
     * Create relationship that represents resource.
     *
     * @param SchemaContainerInterface  $schemaContainer
     * @param PositionInterface         $position
     * @param mixed                     $resource
     *
     * @return RelationshipDataInterface
     */
    public function createRelationshipDataIsResource(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        $resource
    ): RelationshipDataInterface;

    /**
     * Create relationship that represents identifier.
     *
     * @param SchemaContainerInterface  $schemaContainer
     * @param PositionInterface         $position
     * @param SchemaIdentifierInterface $identifier
     *
     * @return RelationshipDataInterface
     */
    public function createRelationshipDataIsIdentifier(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        SchemaIdentifierInterface $identifier
    ): RelationshipDataInterface;

    /**
     * Create relationship that represents collection.
     *
     * @param SchemaContainerInterface $schemaContainer
     * @param PositionInterface        $position
     * @param iterable                 $resources
     *
     * @return RelationshipDataInterface
     */
    public function createRelationshipDataIsCollection(
        SchemaContainerInterface $schemaContainer,
        PositionInterface $position,
        iterable $resources
    ): RelationshipDataInterface;

    /**
     * Create relationship that represents `null`.
     *
     * @return RelationshipDataInterface
     */
    public function createRelationshipDataIsNull(): RelationshipDataInterface;

    /**
     * Create media type.
     *
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     *
     * @return MediaTypeInterface
     */
    public function createMediaType(string $type, string $subType, array $parameters = null): MediaTypeInterface;

    /**
     * Create media type for Accept HTTP header.
     *
     * @param int    $position
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     * @param float  $quality
     *
     * @return AcceptMediaTypeInterface
     */
    public function createAcceptMediaType(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0
    ): AcceptMediaTypeInterface;
}
