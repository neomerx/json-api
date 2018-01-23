<?php namespace Neomerx\Tests\JsonApi\Data;

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

use Closure;
use Neomerx\JsonApi\Schema\BaseSchema;

/**
 * Base schema provider for testing/development purposes. It's not intended to be used in production.
 *
 * @package Neomerx\Tests\JsonApi
 */
abstract class DevSchema extends BaseSchema
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
     * @var string[]
     */
    private $includePaths = [];

    /**
     * @var mixed
     */
    private $relationshipsMeta;

    /**
     * @var Closure
     */
    private $resourceLinksClosure = null;

    /**
     * Return links in relationships instead of data when resource is primary.
     *
     * @var bool
     */
    private $isLinksInPrimary = false;

    /**
     * Return links in relationships instead of data when resource is included.
     *
     * @var bool
     */
    private $isLinksInIncluded = false;

    /**
     * @inheritdoc
     */
    public function getRelationshipsPrimaryMeta($resource)
    {
        return $this->relationshipsMeta ?: parent::getRelationshipsPrimaryMeta($resource);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsInclusionMeta($resource)
    {
        return $this->relationshipsMeta ?: parent::getRelationshipsInclusionMeta($resource);
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource): array
    {
        if (($linksClosure = $this->resourceLinksClosure) === null) {
            return parent::getResourceLinks($resource);
        } else {
            return $linksClosure($resource);
        }
    }

    /**
     * @param array $relationshipMeta
     */
    public function setRelationshipsMeta($relationshipMeta)
    {
        $this->relationshipsMeta = $relationshipMeta;
    }

    /**
     * @param Closure $linksClosure
     */
    public function setResourceLinksClosure(Closure $linksClosure)
    {
        $this->resourceLinksClosure = $linksClosure;
    }

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
        assert(is_string($name) && is_string($key));
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
        assert(is_string($name) && is_string($key));
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
        assert(is_string($name));
        $this->linkRemove[] = $name;
    }

    /**
     * Get include paths.
     *
     * @return string[]
     */
    public function getIncludePaths(): array
    {
        return empty($this->includePaths) === false ? $this->includePaths : parent::getIncludePaths();
    }

    /**
     * Set include paths.
     *
     * @param string[] $includePaths
     */
    public function setIncludePaths($includePaths)
    {
        $this->includePaths = $includePaths;
    }

    /**
     * @return boolean
     */
    public function isIsLinksInPrimary()
    {
        return $this->isLinksInPrimary;
    }

    /**
     * @param boolean $isLinksInPrimary
     */
    public function setIsLinksInPrimary($isLinksInPrimary)
    {
        $this->isLinksInPrimary = $isLinksInPrimary;
    }

    /**
     * @return boolean
     */
    public function isIsLinksInIncluded()
    {
        return $this->isLinksInIncluded;
    }

    /**
     * @param boolean $isLinksInIncluded
     */
    public function setIsLinksInIncluded($isLinksInIncluded)
    {
        $this->isLinksInIncluded = $isLinksInIncluded;
    }

    /**
     * Add/remove values in input array.
     *
     * @param object $resource
     *
     * @param array $links
     */
    protected function fixLinks($resource, array &$links)
    {
        foreach ($this->linkAddTo as list($name, $key, $value)) {
            if ($key === self::LINKS) {
                foreach ($value as $linkKey => $linkOrClosure) {
                    $link = $linkOrClosure instanceof Closure ? $linkOrClosure($this, $resource) : $linkOrClosure;
                    $links[$name][$key][$linkKey] = $link;
                }
            } else {
                $links[$name][$key] = $value;
            }
        }

        foreach ($this->linkRemoveFrom as list($name, $key)) {
            unset($links[$name][$key]);
        }

        foreach ($this->linkRemove as $key) {
            unset($links[$key]);
        }
    }
}
