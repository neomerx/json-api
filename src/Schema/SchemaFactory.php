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

use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class SchemaFactory implements SchemaFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createContainer(array $providers = [])
    {
        return new Container($this, $providers);
    }

    /**
     * @inheritdoc
     */
    public function createResourceObject(
        $isInArray,
        $type,
        $idx,
        array $attributes,
        $meta,
        $selfUrl,
        $isShowSelf,
        $isShowMeta,
        $isShowSelfInIncluded,
        $isShowRelShipsInIncluded,
        $isShowMetaInIncluded,
        $isShowMetaInRlShips
    ) {
        return new ResourceObject(
            $isInArray,
            $type,
            $idx,
            $attributes,
            $meta,
            $selfUrl,
            $isShowSelf,
            $isShowMeta,
            $isShowSelfInIncluded,
            $isShowRelShipsInIncluded,
            $isShowMetaInIncluded,
            $isShowMetaInRlShips
        );
    }

    /**
     * @inheritdoc
     */
    public function createRelationshipObject(
        $name,
        $data,
        LinkInterface $selfLink,
        LinkInterface $relatedLink,
        $isShowAsRef,
        $isShowSelf,
        $isShowRelated,
        $isShowData,
        $isShowMeta,
        $isShowPagination,
        $pagination
    ) {
        return new RelationshipObject(
            $name,
            $data,
            $selfLink,
            $relatedLink,
            $isShowAsRef,
            $isShowSelf,
            $isShowRelated,
            $isShowData,
            $isShowMeta,
            $isShowPagination,
            $pagination
        );
    }

    /**
     * @inheritdoc
     */
    public function createPaginationLinks(
        LinkInterface $firstUrl = null,
        LinkInterface $lastUrl = null,
        LinkInterface $prevUrl = null,
        LinkInterface $nextUrl = null
    ) {
        return new PaginationLinks($firstUrl, $lastUrl, $prevUrl, $nextUrl);
    }

    /**
     * @inheritdoc
     */
    public function createLink($subHref, $meta = null)
    {
        return new Link($subHref, $meta);
    }
}
