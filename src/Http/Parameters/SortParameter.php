<?php namespace Neomerx\JsonApi\Http\Parameters;

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

use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Http\Parameters\SortParameterInterface;

/**
 * @package Neomerx\JsonApi
 */
class SortParameter implements SortParameterInterface
{
    /**
     * @var string
     */
    private $sortField;

    /**
     * @var bool
     */
    private $isAscending;

    /**
     * @param string $sortField
     * @param bool   $isAscending
     */
    public function __construct($sortField, $isAscending)
    {
        is_string($sortField) === true ?: Exceptions::throwInvalidArgument('sortField', $sortField);
        is_bool($isAscending) === true ?: Exceptions::throwInvalidArgument('isAscending', $isAscending);

        $this->sortField   = $sortField;
        $this->isAscending = $isAscending;
    }

    /**
     * @inheritdoc
     */
    public function getField()
    {
        return $this->sortField;
    }

    /**
     * @inheritdoc
     */
    public function isAscending()
    {
        return $this->isAscending;
    }

    /**
     * @inheritdoc
     */
    public function isDescending()
    {
        return !$this->isAscending;
    }
}
