<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue81;

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

use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\Container;

/**
 * @package Neomerx\Tests\JsonApi
 */
class SchemaContainer extends Container
{
    /**
     * @inheritdoc
     */
    protected function createSchemaFromCallable(callable $callable): SchemaInterface
    {
        $schema = call_user_func($callable, $this->getFactory(), $this);

        return $schema;
    }

    /**
     * @inheritdoc
     */
    protected function createSchemaFromClassName(string $className): SchemaInterface
    {
        $schema = new $className($this->getFactory(), $this);

        return $schema;
    }
}
