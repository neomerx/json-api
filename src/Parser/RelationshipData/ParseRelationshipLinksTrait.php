<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Parser\RelationshipData;

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

use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;

/**
 * @package Neomerx\JsonApi
 */
trait ParseRelationshipLinksTrait
{
    /**
     * @param SchemaInterface $parentSchema
     * @param mixed           $parentData
     * @param string          $name
     * @param array           $description
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseRelationshipLinks(
        SchemaInterface $parentSchema,
        $parentData,
        string $name,
        array $description
    ): array {
        $addSelfLink    = $description[SchemaInterface::RELATIONSHIP_LINKS_SELF] ??
            $parentSchema->isAddSelfLinkInRelationshipByDefault($name);
        $addRelatedLink = $description[SchemaInterface::RELATIONSHIP_LINKS_RELATED] ??
            $parentSchema->isAddRelatedLinkInRelationshipByDefault($name);
        \assert(\is_bool($addSelfLink) === true || $addSelfLink instanceof LinkInterface);
        \assert(\is_bool($addRelatedLink) === true || $addRelatedLink instanceof LinkInterface);

        $schemaLinks = $description[SchemaInterface::RELATIONSHIP_LINKS] ?? [];
        \assert(\is_array($schemaLinks));

        // if `self` or `related` link was given as LinkInterface merge it with the other links
        $extraSchemaLinks = null;
        if (\is_bool($addSelfLink) === false) {
            $extraSchemaLinks[LinkInterface::SELF] = $addSelfLink;
            $addSelfLink                           = false;
        }
        if (\is_bool($addRelatedLink) === false) {
            $extraSchemaLinks[LinkInterface::RELATED] = $addRelatedLink;
            $addRelatedLink                           = false;
        }
        if (empty($extraSchemaLinks) === false) {
            // IDE do not understand it's defined without he line below
            \assert(isset($extraSchemaLinks));
            $schemaLinks = \array_merge($extraSchemaLinks, $schemaLinks);
            unset($extraSchemaLinks);
        }
        \assert(\is_bool($addSelfLink) === true && \is_bool($addRelatedLink) === true);

        $hasLinks = $addSelfLink === true || $addRelatedLink === true || empty($schemaLinks) === false;
        $links    = $hasLinks === true ?
            $this->parseLinks($parentSchema, $parentData, $name, $schemaLinks, $addSelfLink, $addRelatedLink) : null;

        return [$hasLinks, $links];
    }

    /**
     * @param SchemaInterface $parentSchema
     * @param mixed           $parentData
     * @param string          $relationshipName
     * @param iterable        $schemaLinks
     * @param bool            $addSelfLink
     * @param bool            $addRelatedLink
     *
     * @return iterable
     */
    private function parseLinks(
        SchemaInterface $parentSchema,
        $parentData,
        string $relationshipName,
        iterable $schemaLinks,
        bool $addSelfLink,
        bool $addRelatedLink
    ): iterable {
        $gotSelf    = false;
        $gotRelated = false;

        foreach ($schemaLinks as $name => $link) {
            \assert($link instanceof LinkInterface);
            if ($name === LinkInterface::SELF) {
                \assert($gotSelf === false);
                $gotSelf     = true;
                $addSelfLink = false;
            } elseif ($name === LinkInterface::RELATED) {
                \assert($gotRelated === false);
                $gotRelated     = true;
                $addRelatedLink = false;
            }

            yield $name => $link;
        }

        if ($addSelfLink === true) {
            $link = $parentSchema->getRelationshipSelfLink($parentData, $relationshipName);
            yield LinkInterface::SELF => $link;
            $gotSelf = true;
        }
        if ($addRelatedLink === true) {
            $link = $parentSchema->getRelationshipRelatedLink($parentData, $relationshipName);
            yield LinkInterface::RELATED => $link;
            $gotRelated = true;
        }

        // spec: check links has at least one of the following: self or related
        \assert($gotSelf || $gotRelated);
    }
}
