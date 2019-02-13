<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Schema;

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

use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Exceptions\LogicException;

/**
 * @package Neomerx\JsonApi
 */
abstract class BaseSchema implements SchemaInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var null|string
     */
    private $subUrl = null;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function getSelfLink($resource): LinkInterface
    {
        return $this->factory->createLink(true, $this->getSelfSubUrl($resource), false);
    }

    /**
     * @inheritdoc
     */
    public function getLinks($resource): iterable
    {
        $links = [
            LinkInterface::SELF => $this->getSelfLink($resource),
        ];

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipSelfLink($resource, string $name): LinkInterface
    {
        // Feel free to override this method to change default URL or add meta

        $url = $this->getSelfSubUrl($resource) . '/' . DocumentInterface::KEYWORD_RELATIONSHIPS . '/' . $name;

        return $this->factory->createLink(true, $url, false);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipRelatedLink($resource, string $name): LinkInterface
    {
        // Feel free to override this method to change default URL or add meta

        $url = $this->getSelfSubUrl($resource) . '/' . $name;

        return $this->factory->createLink(true, $url, false);
    }

    /**
     * @inheritdoc
     */
    public function hasIdentifierMeta($resource): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getIdentifierMeta($resource)
    {
        // default schema does not provide any meta
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getResourceMeta($resource)
    {
        // default schema does not provide any meta
        throw new LogicException();
    }

    /**
     * @inheritdoc
     */
    public function isAddSelfLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isAddRelatedLinkInRelationshipByDefault(string $relationshipName): bool
    {
        return true;
    }

    /**
     * @return FactoryInterface
     */
    protected function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    /**
     * Get resources sub-URL.
     *
     * @return string
     */
    protected function getResourcesSubUrl(): string
    {
        if ($this->subUrl === null) {
            $this->subUrl = '/' . $this->getType();
        }

        return $this->subUrl;
    }

    /**
     * @param mixed $resource
     *
     * @return string
     */
    protected function getSelfSubUrl($resource): string
    {
        return $this->getResourcesSubUrl() . '/' . $this->getId($resource);
    }
}
