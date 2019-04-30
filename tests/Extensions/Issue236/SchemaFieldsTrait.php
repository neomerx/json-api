<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue236;

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
 * @package Neomerx\Tests\JsonApi
 */
trait SchemaFieldsTrait
{
    /**
     * @var SchemaFields
     */
    private $schemaFields;

    /**
     * @return SchemaFields
     */
    public function getSchemaFields(): SchemaFields
    {
        return $this->schemaFields;
    }

    /**
     * @param SchemaFields $schemaFields
     *
     * @return void
     */
    public function setSchemaFields(SchemaFields $schemaFields): void
    {
        $this->schemaFields = $schemaFields;
    }
}
