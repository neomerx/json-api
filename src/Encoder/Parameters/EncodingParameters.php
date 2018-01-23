<?php namespace Neomerx\JsonApi\Encoder\Parameters;

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

use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Factories\Exceptions;

/**
 * @package Neomerx\JsonApi
 */
class EncodingParameters implements EncodingParametersInterface
{
    /**
     * @var array|null
     */
    private $includePaths;

    /**
     * @var array|null
     */
    private $fieldSets;

    /**
     * @param string[]|null $includePaths
     * @param array|null    $fieldSets
     */
    public function __construct(array $includePaths = null, array $fieldSets = null)
    {
        $this->fieldSets    = $fieldSets;
        $this->includePaths = $includePaths;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths(): ?array
    {
        return $this->includePaths;
    }

    /**
     * @inheritdoc
     */
    public function getFieldSets(): ?array
    {
        return $this->fieldSets;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getFieldSet(string $type): ?array
    {
        is_string($type) === true ?: Exceptions::throwInvalidArgument('type', $type);

        return (isset($this->fieldSets[$type]) === true ? $this->fieldSets[$type] : null);
    }
}
