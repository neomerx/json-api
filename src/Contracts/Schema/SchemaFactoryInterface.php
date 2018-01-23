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

use Closure;
use Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
interface SchemaFactoryInterface
{
    /**
     * Create schema provider container.
     *
     * @param array $providers
     *
     * @return ContainerInterface
     */
    public function createContainer(array $providers = []): ContainerInterface;

    /**
     * Create adapter for schema provider container that returns 'resource identifiers' schemes.
     *
     * @param ContainerInterface $container
     *
     * @return ContainerInterface
     */
    public function createResourceIdentifierContainerAdapter(ContainerInterface $container);

    /**
     * Create resource object.
     *
     * @param SchemaInterface $schema
     * @param object          $resource
     * @param bool            $isInArray
     * @param array<string,int>|null  $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject(
        SchemaInterface $schema,
        $resource,
        bool $isInArray,
        array $fieldKeysFilter = null
    ): ResourceObjectInterface;

    /**
     * Create relationship object.
     *
     * @param string|null       $name
     * @param object|array|null $data
     * @param array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface> $links
     * @param mixed             $meta
     * @param bool              $isShowData
     * @param bool              $isRoot
     *
     * @return RelationshipObjectInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function createRelationshipObject(
        ?string $name,
        $data,
        array $links,
        $meta,
        bool $isShowData,
        bool $isRoot
    ): RelationshipObjectInterface;

    /**
     * Create link.
     *
     * @param string            $subHref
     * @param array|object|null $meta
     * @param bool              $treatAsHref If $subHref is a full URL and must not be concatenated with other URLs.
     *
     * @return LinkInterface
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function createLink(string $subHref, $meta = null, bool $treatAsHref = false): LinkInterface;

    /**
     * Create an adapter for schema that will provide data to encode them as resource identifiers.
     *
     * @param SchemaInterface $schema
     *
     * @return SchemaInterface
     */
    public function createResourceIdentifierSchemaAdapter(SchemaInterface $schema): SchemaInterface;

    /**
     * Create schema for identity objects.
     *
     * @param ContainerInterface $container
     * @param string             $classType
     * @param Closure            $identityClosure function($resource) : string
     *
     * @return SchemaInterface
     */
    public function createIdentitySchema(
        ContainerInterface $container,
        string $classType,
        Closure $identityClosure
    ): SchemaInterface;
}
