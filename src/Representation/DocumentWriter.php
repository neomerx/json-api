<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Representation;

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

use Neomerx\JsonApi\Contracts\Parser\IdentifierInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipDataInterface;
use Neomerx\JsonApi\Contracts\Parser\RelationshipInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Representation\DocumentWriterInterface;
use Neomerx\JsonApi\Contracts\Representation\FieldSetFilterInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;

/**
 * @package Neomerx\JsonApi
 */
class DocumentWriter extends BaseWriter implements DocumentWriterInterface
{
    /**
     * @var array
     */
    private $addedResources;

    /**
     * @inheritdoc
     */
    public function setNullToData(): DocumentWriterInterface
    {
        // check data has not been added yet
        \assert(isset($this->data[DocumentInterface::KEYWORD_DATA]) === false);
        $this->data[DocumentInterface::KEYWORD_DATA] = null;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addIdentifierToData(IdentifierInterface $identifier): DocumentWriterInterface
    {
        $this->addToData($this->getIdentifierRepresentation($identifier));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addResourceToData(
        ResourceInterface $resource,
        FieldSetFilterInterface $filter
    ): DocumentWriterInterface {
        $this->addToData($this->getResourceRepresentation($resource, $filter));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addResourceToIncluded(
        ResourceInterface $resource,
        FieldSetFilterInterface $filter
    ): DocumentWriterInterface {
        // We track resources only in included section to avoid duplicates there.
        // If those resources duplicate main it is not bad because if we remove them
        // (and sometimes we would have to rollback and remove some of them if we meet it in the main resources)
        // the client app will have to search them not only in included section but in the main as well.
        //
        // The spec seems to be OK with it.

        if ($this->hasNotBeenAdded($resource) === true) {
            $this->registerResource($resource);
            $this->addToIncluded($this->getResourceRepresentation($resource, $filter));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function reset(): void
    {
        parent::reset();

        $this->addedResources = [];
    }

    /**
     * If full resource has not been added yet either to includes section.
     *
     * @param ResourceInterface $resource
     *
     * @return bool
     */
    protected function hasNotBeenAdded(ResourceInterface $resource): bool
    {
        return isset($this->addedResources[$resource->getId()][$resource->getType()]) === false;
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return void
     */
    protected function registerResource(ResourceInterface $resource): void
    {
        \assert($this->hasNotBeenAdded($resource));

        $this->addedResources[$resource->getId()][$resource->getType()] = true;
    }

    /**
     * @param IdentifierInterface $identifier
     *
     * @return array
     */
    protected function getIdentifierRepresentation(IdentifierInterface $identifier): array
    {
        // it's odd not to have actual ID for identifier (which is OK for newly created resource).
        \assert($identifier->getId() !== null);

        return $identifier->hasIdentifierMeta() === false ? [
            DocumentInterface::KEYWORD_TYPE => $identifier->getType(),
            DocumentInterface::KEYWORD_ID   => $identifier->getId(),
        ] : [
            DocumentInterface::KEYWORD_TYPE => $identifier->getType(),
            DocumentInterface::KEYWORD_ID   => $identifier->getId(),
            DocumentInterface::KEYWORD_META => $identifier->getIdentifierMeta(),
        ];
    }

    /**
     * @param ResourceInterface $resource
     *
     * @return array
     */
    protected function getIdentifierRepresentationFromResource(ResourceInterface $resource): array
    {
        return $resource->hasIdentifierMeta() === false ? [
            DocumentInterface::KEYWORD_TYPE => $resource->getType(),
            DocumentInterface::KEYWORD_ID   => $resource->getId(),
        ] : [
            DocumentInterface::KEYWORD_TYPE => $resource->getType(),
            DocumentInterface::KEYWORD_ID   => $resource->getId(),
            DocumentInterface::KEYWORD_META => $resource->getIdentifierMeta(),
        ];
    }

    /**
     * @param iterable $attributes
     *
     * @return array
     */
    protected function getAttributesRepresentation(iterable $attributes): array
    {
        $representation = [];
        foreach ($attributes as $name => $value) {
            $representation[$name] = $value;
        }

        return $representation;
    }

    /**
     * @param iterable $relationships
     *
     * @return array
     */
    protected function getRelationshipsRepresentation(iterable $relationships): array
    {
        $representation = [];
        foreach ($relationships as $name => $relationship) {
            \assert(\is_string($name) === true && empty($name) === false);
            \assert($relationship instanceof RelationshipInterface);
            $representation[$name] = $this->getRelationshipRepresentation($relationship);
        }

        return $representation;
    }

    /**
     * @param RelationshipInterface $relationship
     *
     * @return array
     */
    protected function getRelationshipRepresentation(RelationshipInterface $relationship): array
    {
        $representation = [];

        if ($relationship->hasLinks() === true) {
            $representation[DocumentInterface::KEYWORD_LINKS] =
                $this->getLinksRepresentation($this->getUrlPrefix(), $relationship->getLinks());
        }

        if ($relationship->hasData() === true) {
            $representation[DocumentInterface::KEYWORD_DATA] = $this->getRelationshipDataRepresentation(
                $relationship->getData()
            );
        }

        if ($relationship->hasMeta() === true) {
            $representation[DocumentInterface::KEYWORD_META] = $relationship->getMeta();
        }

        return $representation;
    }

    /**
     * @param RelationshipDataInterface $data
     *
     * @return array|null
     */
    protected function getRelationshipDataRepresentation(RelationshipDataInterface $data): ?array
    {
        if ($data->isResource() === true) {
            return $this->getIdentifierRepresentationFromResource($data->getResource());
        } elseif ($data->isIdentifier() === true) {
            return $this->getIdentifierRepresentation($data->getIdentifier());
        } elseif ($data->isCollection() === true) {
            $representation = [];
            foreach ($data->getIdentifiers() as $identifier) {
                \assert($identifier instanceof IdentifierInterface);
                $representation[] = $this->getIdentifierRepresentation($identifier);
            }

            return $representation;
        }

        \assert($data->isNull() === true);

        return null;
    }

    /**
     * @param ResourceInterface       $resource
     * @param FieldSetFilterInterface $filter
     *
     * @return array
     */
    protected function getResourceRepresentation(ResourceInterface $resource, FieldSetFilterInterface $filter): array
    {
        $representation = [
            DocumentInterface::KEYWORD_TYPE => $resource->getType(),
        ];

        if (($index = $resource->getId()) !== null) {
            $representation[DocumentInterface::KEYWORD_ID] = $index;
        }

        $attributes = $this->getAttributesRepresentation($filter->getAttributes($resource));
        if (empty($attributes) === false) {
            \assert(
                \json_encode($attributes) !== false,
                'Attributes for resource type `' . $resource->getType() .
                '` cannot be converted into JSON. Please check its Schema returns valid data.'
            );
            $representation[DocumentInterface::KEYWORD_ATTRIBUTES] = $attributes;
        }

        $relationships = $this->getRelationshipsRepresentation($filter->getRelationships($resource));
        if (empty($relationships) === false) {
            \assert(
                \json_encode($relationships) !== false,
                'Relationships for resource type `' . $resource->getType() .
                '` cannot be converted into JSON. Please check its Schema returns valid data.'
            );
            $representation[DocumentInterface::KEYWORD_RELATIONSHIPS] = $relationships;
        }

        if ($resource->hasLinks() === true) {
            $links = $this->getLinksRepresentation($this->getUrlPrefix(), $resource->getLinks());
            \assert(
                \json_encode($links) !== false,
                'Links for resource type `' . $resource->getType() .
                '` cannot be converted into JSON. Please check its Schema returns valid data.'
            );
            $representation[DocumentInterface::KEYWORD_LINKS] = $links;
        }

        if ($resource->hasResourceMeta() === true) {
            $meta = $resource->getResourceMeta();
            \assert(
                \json_encode($meta) !== false,
                'Meta for resource type `' . $resource->getType() .
                '` cannot be converted into JSON. Please check its Schema returns valid data.'
            );
            $representation[DocumentInterface::KEYWORD_META] = $meta;
        }

        return $representation;
    }

    /**
     * @param array $representation
     *
     * @return void
     */
    private function addToData(array $representation): void
    {
        if ($this->isDataAnArray() === true) {
            $this->data[DocumentInterface::KEYWORD_DATA][] = $representation;

            return;
        }

        // check data has not been added yet
        \assert(\array_key_exists(DocumentInterface::KEYWORD_DATA, $this->data) === false);
        $this->data[DocumentInterface::KEYWORD_DATA] = $representation;
    }

    /**
     * @param array $representation
     *
     * @return void
     */
    private function addToIncluded(array $representation): void
    {
        $this->data[DocumentInterface::KEYWORD_INCLUDED][] = $representation;
    }
}
