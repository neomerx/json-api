<?php namespace Neomerx\JsonApi\Document\Presenters;

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
use Neomerx\JsonApi\Contracts\Document\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
use Neomerx\JsonApi\Contracts\Schema\ResourceObjectInterface;
use Neomerx\JsonApi\Document\Document;
use Neomerx\JsonApi\Factories\Exceptions;
use function Neomerx\JsonApi\I18n\translate as _;

/**
 * This is an auxiliary class for Document that help presenting elements.
 *
 * @package Neomerx\JsonApi
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class ElementPresenter
{
    /**
     * @var Document
     */
    private $document;

    /**
     * Message code.
     */
    const MSG_INVALID_RELATIONSHIP = 0;

    /**
     * Message code.
     */
    const MSG_INVALID_ATTRIBUTE = self::MSG_INVALID_RELATIONSHIP + 1;

    /**
     * Default messages.
     */
    const MESSAGES = [
        self::MSG_INVALID_RELATIONSHIP =>
            '\'%s\' is a reserved keyword and cannot be used as a relationship name in type \'%s\'',
        self::MSG_INVALID_ATTRIBUTE    =>
            '\'%s\' is a reserved keyword and cannot be used as attribute name in type \'%s\'',
    ];

    /**
     * @var array
     */
    private $messages;

    /**
     * @param Document $document
     * @param array    $messages
     */
    public function __construct(Document $document, $messages = self::MESSAGES)
    {
        $this->document = $document;
        $this->messages = $messages;
    }

    /**
     * @param array                       $target
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     * @param mixed                       $value
     *
     * @return void
     */
    public function setRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation,
        $value
    ): void {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $name         = $relation->getName();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent object might be already fully parsed (with children) so
        // - it won't exist in $target
        // - it won't make any sense to parse it again (we'll got exactly the same result and it will be thrown away
        //   as duplicate relations/included resources are not allowed)
        if ($parentExists === true) {
            $isOk = isset($target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name]) === false;
            $isOk ?: Exceptions::throwLogicException();

            $representation = [];

            if ($relation->isShowData() === true) {
                $representation[Document::KEYWORD_LINKAGE_DATA] = $value;
            }

            $representation += $this->getRelationRepresentation($parent, $relation);

            $target[$parentType][$parentId][Document::KEYWORD_RELATIONSHIPS][$name] = $representation;
        }
    }

    /**
     * @param array                       $target
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     * @param ResourceObjectInterface     $resource
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function addRelationshipTo(
        array &$target,
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation,
        ResourceObjectInterface $resource
    ): void {
        $parentId     = $parent->getId();
        $parentType   = $parent->getType();
        $parentExists = isset($target[$parentType][$parentId]);

        // parent might be already added to included to it won't be in 'target' buffer
        if ($parentExists === true) {
            $parentAlias = &$target[$parentType][$parentId];

            $name               = $relation->getName();
            $alreadyGotRelation = isset($parentAlias[Document::KEYWORD_RELATIONSHIPS][$name]);

            $linkage = null;
            if ($relation->isShowData() === true) {
                $linkage = $this->getLinkageRepresentation($resource);
            }

            if ($alreadyGotRelation === false) {
                // ... add the first linkage
                $representation = [];
                if ($linkage !== null) {
                    if ($resource->isInArray() === true) {
                        // original data in array
                        $representation[Document::KEYWORD_LINKAGE_DATA][] = $linkage;
                    } else {
                        // original data not in array (just object)
                        $representation[Document::KEYWORD_LINKAGE_DATA] = $linkage;
                    }
                }
                $representation += $this->getRelationRepresentation($parent, $relation);

                $parentAlias[Document::KEYWORD_RELATIONSHIPS][$name] = $representation;
            } elseif ($alreadyGotRelation === true && $linkage !== null) {
                // Check data in '$name' relationship are marked as not arrayed otherwise
                // it's fail to add multiple data instances
                $resource->isInArray() === true ?: Exceptions::throwLogicException();

                // ... or add another linkage
                $parentAlias[Document::KEYWORD_RELATIONSHIPS][$name][Document::KEYWORD_LINKAGE_DATA][] = $linkage;
            }
        }
    }

    /**
     * Convert resource object for 'data' section to array.
     *
     * @param ResourceObjectInterface $resource
     * @param bool                    $isShowAttributes
     *
     * @return array
     */
    public function convertDataResourceToArray(ResourceObjectInterface $resource, bool $isShowAttributes): array
    {
        return $this->convertResourceToArray(
            $resource,
            $resource->getResourceLinks(),
            $resource->getPrimaryMeta(),
            $isShowAttributes
        );
    }

    /**
     * Convert resource object for 'included' section to array.
     *
     * @param ResourceObjectInterface $resource
     *
     * @return array
     */
    public function convertIncludedResourceToArray(ResourceObjectInterface $resource): array
    {
        return $this->convertResourceToArray(
            $resource,
            $resource->getIncludedResourceLinks(),
            $resource->getInclusionMeta(),
            $resource->isShowAttributesInIncluded()
        );
    }

    /**
     * @param string|null $prefix
     * @param array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>|null $links
     *
     * @return array|null
     */
    public function getLinksRepresentation(string $prefix = null, array $links = null): ?array
    {
        $result = null;
        if (empty($links) === false) {
            foreach ($links as $name => $link) {
                /** @var LinkInterface $link */
                $result[$name] = $this->getLinkRepresentation($prefix, $link);
            }
        }

        return $result;
    }

    /**
     * @param ResourceObjectInterface $resource
     *
     * @return array<string,string>
     */
    private function getLinkageRepresentation(ResourceObjectInterface $resource): array
    {
        $representation = [
            Document::KEYWORD_TYPE => $resource->getType(),
            Document::KEYWORD_ID   => $resource->getId(),
        ];
        if (($meta = $resource->getLinkageMeta()) !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        return $representation;
    }

    /**
     * @param string|null        $prefix
     * @param LinkInterface $link
     *
     * @return array|null|string
     */
    private function getLinkRepresentation(?string $prefix, LinkInterface $link)
    {
        return $link->hasMeta() === true ? $link->getHrefWithMeta($prefix) : $link->getHref($prefix);
    }

    /**
     * @param ResourceObjectInterface     $parent
     * @param RelationshipObjectInterface $relation
     *
     * @return array
     */
    private function getRelationRepresentation(
        ResourceObjectInterface $parent,
        RelationshipObjectInterface $relation
    ): array {
        $isOk = ($relation->getName() !== Document::KEYWORD_SELF);
        if ($isOk === false) {
            $message = $this->messages[self::MSG_INVALID_RELATIONSHIP];
            throw new InvalidArgumentException(_($message, Document::KEYWORD_SELF, $parent->getType()));
        }

        $representation = [];

        if (($meta = $relation->getMeta()) !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        $baseUrl = $this->document->getUrlPrefix();
        foreach ($relation->getLinks() as $name => $link) {
            $representation[Document::KEYWORD_LINKS][$name] = $this->getLinkRepresentation($baseUrl, $link);
        }

        return $representation;
    }

    /**
     * Convert resource object to array.
     *
     * @param ResourceObjectInterface $resource
     * @param array                   $resourceLinks
     * @param mixed                   $meta
     * @param bool                    $isShowAttributes
     *
     * @return array
     */
    private function convertResourceToArray(
        ResourceObjectInterface $resource,
        array $resourceLinks,
        $meta,
        bool $isShowAttributes
    ): array {
        $representation = [
            Document::KEYWORD_TYPE => $resource->getType(),
        ];
        if (($resourceId = $resource->getId()) !== null) {
            $representation[Document::KEYWORD_ID] = $resourceId;
        }

        $attributes = $resource->getAttributes();

        // "type" and "id" are reserved keywords and cannot be used as resource object attributes
        $isOk = (isset($attributes[Document::KEYWORD_TYPE]) === false);
        if ($isOk === false) {
            $message = $this->messages[self::MSG_INVALID_ATTRIBUTE];
            throw new InvalidArgumentException(_($message, Document::KEYWORD_TYPE, $resource->getType()));
        }
        $isOk = (isset($attributes[Document::KEYWORD_ID]) === false);
        if ($isOk === false) {
            $message = $this->messages[self::MSG_INVALID_ATTRIBUTE];
            throw new InvalidArgumentException(_($message, Document::KEYWORD_ID, $resource->getType()));
        }

        if ($isShowAttributes === true && empty($attributes) === false) {
            $representation[Document::KEYWORD_ATTRIBUTES] = $attributes;
        }

        // reserve placeholder for relationships, otherwise it would be added after
        // links and meta which is not visually beautiful
        $representation[Document::KEYWORD_RELATIONSHIPS] = null;

        if (empty($resourceLinks) === false) {
            foreach ($resourceLinks as $linkName => $link) {
                /** @var LinkInterface $link */
                $representation[Document::KEYWORD_LINKS][$linkName] =
                    $this->getLinkRepresentation($this->document->getUrlPrefix(), $link);
            }
        }

        if ($meta !== null) {
            $representation[Document::KEYWORD_META] = $meta;
        }

        return $representation;
    }
}
