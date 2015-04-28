<?php namespace Neomerx\JsonApi\Contracts\Encoder\Parser;

/**
 * Copyright 2015 info@neomerx.com (www.neomerx.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed for in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use \Neomerx\JsonApi\Contracts\Schema\LinkObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackReadOnlyInterface;

/**
 * @package Neomerx\JsonApi
 */
interface ParserManagerInterface
{
    /**
     * If parser should parse resources at the link.
     *
     * @param LinkObjectInterface    $link
     * @param StackReadOnlyInterface $stack
     *
     * @return bool
     */
    public function isShouldParseResources(LinkObjectInterface $link, StackReadOnlyInterface $stack);

    /**
     * If parser should parse links for the resource.
     *
     * @param ResourceObjectInterface $resource
     * @param bool                    $isCircular
     * @param StackReadOnlyInterface  $stack
     *
     * @return bool
     */
    public function isShouldParseLinks(ResourceObjectInterface $resource, $isCircular, StackReadOnlyInterface $stack);

    /**
     * Get field set for resource. Required fields will be array keys.
     *
     * @param string $type
     *
     * @return array|null
     */
    public function getFieldSet($type);
}
