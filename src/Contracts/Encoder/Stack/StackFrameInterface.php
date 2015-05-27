<?php namespace Neomerx\JsonApi\Contracts\Encoder\Stack;

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

use \Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
interface StackFrameInterface extends StackFrameReadOnlyInterface
{
    /**
     * Set associated resource object.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return void
     */
    public function setResource(ResourceObjectInterface $resource);

    /**
     * Set associated relationship object.
     *
     * @param RelationshipObjectInterface $relationship
     *
     * @return void
     */
    public function setRelationship(RelationshipObjectInterface $relationship);
}
