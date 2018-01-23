<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue47;

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

use InvalidArgumentException;
use Neomerx\JsonApi\Schema\ResourceObject;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CustomResourceObject extends ResourceObject
{
    /**
     * @inheritdoc
     */
    public function getAttributes(): ?array
    {
        $filter     = $this->fieldKeysFilter;
        $attributes = $this->schema->getAttributes($this->resource);

        // in real app here should come filtering however for testing it's ok to make sure we have
        // - attributes
        // - filter params
        // - if we return correct result it will be delivered to to invoking test

        // check we have received attribute filter
        $filterEquals = ($filter === ['private.email' => 0]);
        if ($filterEquals === false) {
            throw new InvalidArgumentException();
        }

        // check we have received attributes
        $attributesEqual = ($attributes === [
                'username' => 'vivalacrowe',
                'private'  => [
                    'email' => 'hello@vivalacrowe.com',
                    'name'  => 'Rob',
                ],
            ]);
        if ($attributesEqual === false) {
            throw new InvalidArgumentException();
        }

        return ['private' => ['email' => 'hello@vivalacrowe.com']];
    }
}
