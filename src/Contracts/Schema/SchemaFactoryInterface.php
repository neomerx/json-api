<?php namespace Neomerx\JsonApi\Contracts\Schema;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
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
interface SchemaFactoryInterface
{
    /**
     * Create schema provider container.
     *
     * @param array $providers
     *
     * @return ContainerInterface
     */
    public function createContainer(array $providers = []);

    /**
     * Create resource object.
     *
     * @param SchemaProviderInterface $schema
     * @param object                  $resource
     * @param bool                    $isInArray
     * @param array<string, int>|null $attributeKeysFilter
     *
     * @return ResourceObjectInterface
     */
    public function createResourceObject(
        SchemaProviderInterface $schema,
        $resource,
        $isInArray,
        array $attributeKeysFilter = null
    );

    /**
     * Create relationship object.
     *
     * @param string               $name
     * @param object|array|null    $data
     * @param array<string,LinkInterface> $links
     * @param mixed                $meta
     * @param bool                 $isShowSelf
     * @param bool                 $isShowRelated
     * @param bool                 $isShowMeta
     * @param bool                 $isShowData
     * @param bool                 $isShowAsRef
     *
     * @return RelationshipObjectInterface
     */
    public function createRelationshipObject(
        $name,
        $data,
        $links,
        $meta,
        $isShowSelf,
        $isShowRelated,
        $isShowMeta,
        $isShowData,
        $isShowAsRef
    );

    /**
     * Create link.
     *
     * @param string            $subHref
     * @param array|object|null $meta
     *
     * @return LinkInterface
     */
    public function createLink($subHref, $meta = null);
}
