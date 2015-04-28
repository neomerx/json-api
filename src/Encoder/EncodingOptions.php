<?php namespace Neomerx\JsonApi\Encoder;

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

use \Neomerx\JsonApi\Contracts\Encoder\EncodingOptionsInterface;

/**
 * @package Neomerx\JsonApi
 */
class EncodingOptions implements EncodingOptionsInterface
{
    /**
     * @var string[]|null
     */
    private $paths;

    /**
     * @var string[][]|null
     */
    private $fieldSets;

    /**
     * @param string[]|null   $paths
     * @param string[][]|null $fieldSets
     */
    public function __construct(array $paths = null, array $fieldSets = null)
    {
        $this->paths     = $paths;
        $this->fieldSets = $fieldSets;
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        return $this->paths;
    }

    /**
     * @inheritdoc
     */
    public function getFieldSet($type)
    {
        assert('is_string($type)');
        if ($this->fieldSets === null) {
            return null;
        } else {
            return (isset($this->fieldSets[$type]) === true ? $this->fieldSets[$type] : []);
        }
    }
}
