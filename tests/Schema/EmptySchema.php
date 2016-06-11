<?php namespace Neomerx\Tests\JsonApi\Schema;

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

use \LogicException;
use \Neomerx\JsonApi\Schema\SchemaProvider;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EmptySchema extends SchemaProvider
{
    /**
     * @var string
     */
    public static $subUrl;

    /**
     * @var string
     */
    public static $type;

    /**
     * @param SchemaFactoryInterface $factory
     */
    public function __construct(SchemaFactoryInterface $factory)
    {
        $this->selfSubUrl   = static::$subUrl;
        $this->resourceType = static::$type;

        parent::__construct($factory);
    }

    /**
     * @inheritdoc
     */
    public function getId($resource)
    {
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($resource)
    {
        throw new LogicException();
    }
}
