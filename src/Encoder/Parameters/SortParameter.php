<?php namespace Neomerx\JsonApi\Encoder\Parameters;

/**
 * Copyright 2015-2017 info@neomerx.com
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
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\SortParameterInterface;

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
     * @var null || string
     */
    private $sortRelationshipAttribute;
    
    /**
     * @var bool
     */
    private $isAscending;


    /**
     * @param $sortField
     * @param $isAscending
     * @param null $sortRelationAttribute
     */
    public function __construct($sortField, $isAscending, $sortRelationshipAttribute=null)
    {
        is_string($sortField) === true ?: Exceptions::throwInvalidArgument('sortField', $sortField);
        is_string($sortRelationshipAttribute) === true || is_null($sortRelationshipAttribute) === true ?: Exceptions::throwInvalidArgument('sortRelationshipAttribute', $sortRelationshipAttribute);
        is_bool($isAscending) === true ?: Exceptions::throwInvalidArgument('isAscending', $isAscending);

        $this->sortField   = $sortField;
        $this->sortRelationshipAttribute = $sortRelationshipAttribute;
        $this->isAscending = $isAscending;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $prefix = $this->isAscending() ? '' : '-';

        return $prefix . $this->getField();
    }

    /**
     * @inheritdoc
     */
    public function getField()
    {
        return $this->sortField;
    }


    /**
     * @return null || string
     */
    public function getRelationshipAttribute()
    {
        return $this->sortRelationshipAttribute;
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
