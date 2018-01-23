<?php namespace Neomerx\JsonApi\Contracts\Schema;

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

use Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
interface SchemaInterface
{
    /**
     * Get resource type.
     *
     * @return string
     */
    public function getResourceType(): string;

    /**
     * Get resource sub URL.
     *
     * @param object|null $resource
     *
     * @return string
     */
    public function getSelfSubUrl($resource = null): string;

    /**
     * Get resource identity.
     *
     * @param object $resource
     *
     * @return string|null
     */
    public function getId($resource): ?string;

    /**
     * Get resource URL link.
     *
     * @param object $resource
     *
     * @return LinkInterface
     */
    public function getSelfSubLink($resource): LinkInterface;

    /**
     * Get 'self' URL link to resource relationship.
     *
     * @param object     $resource
     * @param string     $name
     * @param null|mixed $meta
     * @param bool       $treatAsHref
     *
     * @return LinkInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getRelationshipSelfLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface;

    /**
     * Get 'related' URL link to resource relationship.
     *
     * @param object     $resource
     * @param string     $name
     * @param null|mixed $meta
     * @param bool       $treatAsHref
     *
     * @return LinkInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function getRelationshipRelatedLink(
        $resource,
        string $name,
        $meta = null,
        bool $treatAsHref = false
    ): LinkInterface;

    /**
     * Get resource attributes.
     *
     * @param object     $resource
     * @param array|null $fieldKeysFilter
     *
     * @return array|null
     */
    public function getAttributes($resource, array $fieldKeysFilter = null): ?array;

    /**
     * Get resource relationships.
     *
     * @param            $resource
     * @param bool       $isPrimary
     * @param array      $includeRelationships
     *
     * @return array|null
     */
    public function getRelationships($resource, bool $isPrimary, array $includeRelationships): ?array;

    /**
     * Create resource object.
     *
     * @param object $resource
     * @param bool   $isOriginallyArrayed
     * @param array <string, int>|null $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject(
        $resource,
        bool $isOriginallyArrayed,
        array $fieldKeysFilter = null
    ): ResourceObjectInterface;

    /**
     * Get resource's relationship objects.
     *
     * @param object     $resource
     * @param bool       $isPrimary
     * @param array      $includeRelationships
     *
     * @return iterable RelationshipObjectInterface[]
     */
    public function getRelationshipObjectIterator($resource, bool $isPrimary, array $includeRelationships): iterable;

    /**
     * Get links related to resource.
     *
     * @param mixed $resource
     *
     * @return array Array key is link name and value is LinkInterface.
     */
    public function getResourceLinks($resource): array;

    /**
     * Get links related to resource when it is in 'included' section.
     *
     * @param mixed $resource
     *
     * @return array Array key is link name and value is LinkInterface.
     */
    public function getIncludedResourceLinks($resource): array;

    /**
     * If resource attributes should be shown when the resource is within 'included'.
     *
     * @return bool
     */
    public function isShowAttributesInIncluded(): bool;

    /**
     * Get schema default include paths.
     *
     * @return string[]
     */
    public function getIncludePaths(): array;

    /**
     * Get meta when resource is primary (top level 'data' section).
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getPrimaryMeta($resource);

    /**
     * Get meta when resource is within included resources.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getInclusionMeta($resource);

    /**
     * Get get relationships meta when the resource is primary.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getRelationshipsPrimaryMeta($resource);

    /**
     * Get get relationships meta when the resource is within included.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getRelationshipsInclusionMeta($resource);

    /**
     * Get meta when resource is within relationship of a primary resource.
     *
     * @param object $resource
     *
     * @return mixed
     */
    public function getLinkageMeta($resource);
}
