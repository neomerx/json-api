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

use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface;

/**
 * @package Neomerx\JsonApi
 */
class DocumentFactory implements DocumentFactoryInterface
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
    public function createError(
        $idx = null,
        LinkInterface $aboutLink = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        $source = null,
        array $meta = null
    ) {
        return new Error($idx, $aboutLink, $status, $code, $title, $detail, $source, $meta);
    }
}
