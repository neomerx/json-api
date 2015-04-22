<?php namespace Neomerx\JsonApi\Document;

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

use \Iterator;
use \Neomerx\JsonApi\Contracts\Document\FactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class Factory implements FactoryInterface
{
    /**
     * @inheritdoc
     */
    public function createDocument()
    {
        return new Document();
    }

    /**
     * @inheritdoc
     */
    public function createElement(
        $type,
        $idx,
        array $attributes,
        $selfUrl,
        Iterator $links,
        $meta
    ) {
        return new Element($type, $idx, $attributes, $selfUrl, $links, $meta);
    }

    /**
     * @inheritdoc
     */
    public function createLink(
        $name,
        $isOnlyRelatedRef,
        $type,
        array $linkageIds,
        $selfUrl,
        $relatedUrl,
        $meta
    ) {
        return new Link($name, $isOnlyRelatedRef, $type, $linkageIds, $selfUrl, $relatedUrl, $meta);
    }

    /**
     * @inheritdoc
     */
    public function createError(
        $idx = null,
        $href = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        array $links = null,
        array $paths = null,
        array $members = null
    ) {
        return new Error($idx, $href, $status, $code, $title, $detail, $links, $paths, $members);
    }
}
