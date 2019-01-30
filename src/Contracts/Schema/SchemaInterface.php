<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Schema;

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

/**
 * @package Neomerx\JsonApi
 */
interface SchemaInterface
{
    /** @var int Relationship's data section */
    const RELATIONSHIP_DATA = 0;

    /** @var int Relationship's links section */
    const RELATIONSHIP_LINKS = self::RELATIONSHIP_DATA + 1;

    /** @var int Relationship's meta section */
    const RELATIONSHIP_META = self::RELATIONSHIP_LINKS + 1;

    /** @var int If `self` link should be added in relationship */
    const RELATIONSHIP_LINKS_SELF = self::RELATIONSHIP_META + 1;

    /** @var int If `related` link should be added in relationship */
    const RELATIONSHIP_LINKS_RELATED = self::RELATIONSHIP_LINKS_SELF + 1;

    /**
     * Get resource type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get resource identity. Newly created objects without ID may return `null` to exclude it from encoder output.
     *
     * @param object $resource
     *
     * @return string|null
     */
    public function getId($resource): ?string;

    /**
     * Get resource attributes.
     *
     * @param mixed $resource
     *
     * @return iterable
     */
    public function getAttributes($resource): iterable;

    /**
     * Get resource relationship descriptions.
     *
     * @param mixed $resource
     *
     * @return iterable
     */
    public function getRelationships($resource): iterable;

    /**
     * Get resource sub URL.
     *
     * @param mixed $resource
     *
     * @return LinkInterface
     */
    public function getSelfLink($resource): LinkInterface;

    /**
     * Get resource links.
     *
     * @param mixed $resource
     *
     * @see LinkInterface
     *
     * @return iterable
     */
    public function getLinks($resource): iterable;

    /**
     * Get 'self' URL link to resource relationship.
     *
     * @param mixed  $resource
     * @param string $name
     *
     * @return LinkInterface
     */
    public function getRelationshipSelfLink($resource, string $name): LinkInterface;

    /**
     * Get 'related' URL link to resource relationship.
     *
     * @param mixed  $resource
     * @param string $name
     *
     * @return LinkInterface
     */
    public function getRelationshipRelatedLink($resource, string $name): LinkInterface;

    /**
     * If resource has meta when it is considered as a resource identifier (e.g. in a relationship).
     *
     * @param mixed $resource
     *
     * @return bool
     */
    public function hasIdentifierMeta($resource): bool;

    /**
     * Get resource meta when it is considered as a resource identifier (e.g. in a relationship).
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    public function getIdentifierMeta($resource);

    /**
     * If resource has meta when it is considered as a resource (e.g. in a main data or included sections).
     *
     * @param mixed $resource
     *
     * @return bool
     */
    public function hasResourceMeta($resource): bool;

    /**
     * Get resource meta when it is considered as a resource (e.g. in a main data or included sections).
     *
     * @param mixed $resource
     *
     * @return mixed
     */
    public function getResourceMeta($resource);

    /**
     * If `self` links should be added in relationships by default.
     *
     * @param string $relationshipName
     *
     * @return bool
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool;

    /**
     * If `related` links should be added in relationships by default.
     *
     * @param string $relationshipName
     *
     * @return bool
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool;
}
