<?php namespace Neomerx\JsonApi\Schema;

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

use \InvalidArgumentException;
use \Neomerx\JsonApi\I18n\Translator as T;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

/**
 * @package Neomerx\JsonApi
 */
abstract class SchemaProvider implements SchemaProviderInterface
{
    /** Links information */
    const LINKS = DocumentInterface::KEYWORD_LINKS;

    /** Linked data key. */
    const DATA = DocumentInterface::KEYWORD_DATA;

    /** Relationship meta */
    const META = DocumentInterface::KEYWORD_META;

    /** If 'self' URL should be shown. */
    const SHOW_SELF = 'showSelf';

    /** If 'related' URL should be shown. */
    const SHOW_RELATED = 'related';

    /** If data should be shown in relationships. */
    const SHOW_DATA = 'showData';

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @var string Must end with '/'
     */
    protected $selfSubUrl;

    /**
     * @var bool
     */
    protected $isShowAttributesInIncluded = true;

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param SchemaFactoryInterface $factory
     * @param ContainerInterface     $container
     */
    public function __construct(SchemaFactoryInterface $factory, ContainerInterface $container)
    {
        $isOk = (is_string($this->resourceType) === true && empty($this->resourceType) === false);
        if ($isOk === false) {
            throw new InvalidArgumentException(T::t('Resource type is not set for Schema \'%s\'.', [static::class]));
        }

        if ($this->selfSubUrl === null) {
            $this->selfSubUrl = '/' . $this->resourceType . '/';
        } else {
            $isOk =
                is_string($this->selfSubUrl) === true &&
                empty($this->selfSubUrl) === false &&
                $this->selfSubUrl[0] === '/' &&
                $this->selfSubUrl[strlen($this->selfSubUrl) - 1] == '/';

            if ($isOk === false) {
                $message = T::t('\'Self\' sub-url set incorrectly for Schema \'%s\'.', [static::class]);
                throw new InvalidArgumentException($message);
            }
        }

        $this->factory   = $factory;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubUrl()
    {
        return $this->selfSubUrl;
    }

    /**
     * @inheritdoc
     */
    public function getSelfSubLink($resource)
    {
        return new Link($this->selfSubUrl . $this->getId($resource));
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getLinkageMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getInclusionMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsPrimaryMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipsInclusionMeta($resource)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isShowAttributesInIncluded()
    {
        return $this->isShowAttributesInIncluded;
    }

    /**
     * Get resource links.
     *
     * @param object $resource
     * @param array  $includeRelationships A list of relationships that will be included as full resources.
     *
     * @return array
     */
    public function getRelationships($resource, array $includeRelationships = [])
    {
        $resource ?: null;
        $includeRelationships ?: null;

        return [];
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject($resource, $isOriginallyArrayed, $attributeKeysFilter = null)
    {
        return $this->factory->createResourceObject($this, $resource, $isOriginallyArrayed, $attributeKeysFilter);
    }

    /**
     * @inheritdoc
     */
    public function getRelationshipObjectIterator($resource, array $includeRelationships)
    {
        foreach ($this->getRelationships($resource, $includeRelationships) as $name => $desc) {
            yield $this->createRelationshipObject($name, $desc);
        }
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getResourceLinks($resource)
    {
        $links = [
            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource)
    {
        return [];
    }

    /**
     * @param string $relationshipName
     * @param array  $description
     * @param bool   $isShowSelf
     * @param bool   $isShowRelated
     *
     * @return array <string,LinkInterface>
     */
    protected function readLinks($relationshipName, array $description, $isShowSelf, $isShowRelated)
    {
        $links = $this->getValue($description, self::LINKS, []);
        if ($isShowSelf === true && isset($links[LinkInterface::SELF]) === false) {
            $links[LinkInterface::SELF] = $this->factory->createLink(
                DocumentInterface::KEYWORD_RELATIONSHIPS. '/'.$relationshipName
            );
        }
        if ($isShowRelated === true && isset($links[LinkInterface::RELATED]) === false) {
            $links[LinkInterface::RELATED] = $this->factory->createLink($relationshipName);
        }

        return $links;
    }

    /**
     * @param string $name
     * @param array  $desc
     *
     * @return RelationshipObjectInterface
     */
    protected function createRelationshipObject($name, array $desc)
    {
        $data          = $this->getValue($desc, self::DATA);
        $meta          = $this->getValue($desc, self::META, null);
        $isShowSelf    = ($this->getValue($desc, self::SHOW_SELF, false) === true);
        $isShowRelated = ($this->getValue($desc, self::SHOW_RELATED, false) === true);
        $isShowData    = ($this->getValue($desc, self::SHOW_DATA, true) === true);
        $links         = $this->readLinks($name, $desc, $isShowSelf, $isShowRelated);

        return $this->factory->createRelationshipObject($name, $data, $links, $meta, $isShowData, false);
    }

    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function getValue(array $array, $key, $default = null)
    {
        return (isset($array[$key]) === true ? $array[$key] : $default);
    }
}
