<?php namespace Neomerx\Tests\JsonApi\Data;

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

use \Neomerx\JsonApi\Schema\SchemaProvider;

/**
 * Base schema provider for testing/development purposes. It's not intended to be used in production.
 *
 * @package Neomerx\Tests\JsonApi
 */
abstract class DevSchemaProvider extends SchemaProvider
{
    /**
     * @var array
     */
    private $linkAddTo = [];

    /**
     * @var array
     */
    private $linkRemoveFrom = [];

    /**
     * @var array
     */
    private $linkRemove = [];

    /**
     * Add to 'add to link' list.
     *
     * @param string $name
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function linkAddTo($name, $key, $value)
    {
        assert('is_string($name) && is_string($key)');
        $this->linkAddTo[] = [$name, $key, $value];
    }

    /**
     * Add to 'remove from link' list.
     *
     * @param string $name
     * @param string $key
     *
     * @return void
     */
    public function linkRemoveFrom($name, $key)
    {
        assert('is_string($name) && is_string($key)');
        $this->linkRemoveFrom[] = [$name, $key];
    }

    /**
     * Add to 'remove link' list.
     *
     * @param string $name
     *
     * @return void
     */
    public function linkRemove($name)
    {
        assert('is_string($name)');
        $this->linkRemove[] = $name;
    }

    /**
     * Set parser's max depth level.
     *
     * @param int $depthLevel
     */
    public function setDefaultParseDepth($depthLevel)
    {
        $this->defaultParseDepth = $depthLevel;
    }

    /**
     * Add/remove values in input array.
     *
     * @param array $links
     */
    protected function fixLinks(array &$links)
    {
        foreach ($this->linkAddTo as list($name, $key, $value)) {
            $links[$name][$key] = $value;
        }

        foreach ($this->linkRemoveFrom as list($name, $key)) {
            unset($links[$name][$key]);
        }

        foreach ($this->linkRemove as $key) {
            unset($links[$key]);
        }
    }
}
