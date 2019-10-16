<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Schemas;

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

use Closure;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Schema\BaseSchema;
use function assert;
use function is_string;

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
    private $addToRelationship = [];

    /**
     * @var array
     */
    private $removeFromRelationship = [];

    /**
     * @var array
     */
    private $relationshipToRemove = [];

    /**
     * @var Closure
     */
    private $resourceLinksClosure = null;

    /**
     * @inheritdoc
     */
    public function getLinks($resource): array
    {
        if (($linksClosure = $this->resourceLinksClosure) === null) {
            return parent::getLinks($resource);
        } else {
            return $linksClosure($resource);
        }
    }

    /**
     * @param Closure $linksClosure
     *
     * @return void
     */
    public function setResourceLinksClosure(Closure $linksClosure): void
    {
        $this->resourceLinksClosure = $linksClosure;
    }

    /**
     * Add value to relationship description.
     *
     * @param string $name  Relationship name.
     * @param int    $key   Description key.
     * @param mixed  $value Value to add (might be array of links).
     *
     * @return void
     */
    public function addToRelationship(string $name, int $key, $value): void
    {
        $this->addToRelationship[] = [$name, $key, $value];
    }

    /**
     * Remove from relationship description.
     *
     * @param string $name Relationship name.
     * @param int    $key  Description key.
     *
     * @return void
     */
    public function removeFromRelationship(string $name, int $key): void
    {
        $this->removeFromRelationship[] = [$name, $key];
    }

    /**
     * Remove entire relationship from description.
     *
     * @param string $name Relationship name.
     *
     * @return void
     */
    public function removeRelationship(string $name): void
    {
        assert(is_string($name));
        $this->relationshipToRemove[] = $name;
    }

    /**
     * @param mixed $resource
     *
     * @return string
     */
    public function getSelfSubUrl($resource): string
    {
        return parent::getSelfSubUrl($resource);
    }

    /**
     * Hide `self` link in relationship.
     *
     * @param string $name
     */
    public function hideSelfLinkInRelationship(string $name): void
    {
        $this->addToRelationship($name, AuthorSchema::RELATIONSHIP_LINKS_SELF, false);
    }

    /**
     * Set custom `self` link in relationship.
     *
     * @param string        $name
     * @param LinkInterface $link
     *
     * @return void
     */
    public function setSelfLinkInRelationship(string $name, LinkInterface $link): void
    {
        $this->addToRelationship($name, AuthorSchema::RELATIONSHIP_LINKS_SELF, $link);
    }

    /**
     * Hide `related` link in relationship.
     *
     * @param string $name
     */
    public function hideRelatedLinkInRelationship(string $name): void
    {
        $this->addToRelationship($name, AuthorSchema::RELATIONSHIP_LINKS_RELATED, false);
    }

    /**
     * Set custom `related` link in relationship.
     *
     * @param string        $name
     * @param LinkInterface $link
     *
     * @return void
     */
    public function setRelatedLinkInRelationship(string $name, LinkInterface $link): void
    {
        $this->addToRelationship($name, AuthorSchema::RELATIONSHIP_LINKS_RELATED, $link);
    }

    /**
     * @param string $name
     */
    public function hideDefaultLinksInRelationship(string $name): void
    {
        $this->hideSelfLinkInRelationship($name);
        $this->hideRelatedLinkInRelationship($name);
    }

    /**
     * Hide `links` section for resource.
     */
    public function hideResourceLinks(): void
    {
        $this->setResourceLinksClosure(
            function (): array {
                return [];
            }
        );
    }

    /**
     * Add/remove values in input array.
     *
     * @param object $resource
     * @param array  $descriptions
     *
     * @return array
     */
    protected function fixDescriptions($resource, array $descriptions): array
    {
        foreach ($this->addToRelationship as list($name, $key, $value)) {
            if ($key === self::RELATIONSHIP_LINKS) {
                foreach ($value as $linkKey => $linkOrClosure) {
                    $link                                = $linkOrClosure instanceof Closure ? $linkOrClosure(
                        $this,
                        $resource
                    ) : $linkOrClosure;
                    $descriptions[$name][$key][$linkKey] = $link;
                }
            } else {
                $descriptions[$name][$key] = $value;
            }
        }

        foreach ($this->removeFromRelationship as list($name, $key)) {
            unset($descriptions[$name][$key]);
        }

        foreach ($this->relationshipToRemove as $key) {
            unset($descriptions[$key]);
        }

        return $descriptions;
    }
}
