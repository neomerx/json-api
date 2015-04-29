<?php namespace Neomerx\JsonApi\Contracts\Document;

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

/**
 * @package Neomerx\JsonApi
 */
interface DocumentFactoryInterface
{
    /**
     * Create document.
     *
     * @return DocumentInterface
     */
    public function createDocument();

    /**
     * Create error instance.
     *
     * @param int|string|null $idx
     * @param string|null     $href
     * @param string|null     $status
     * @param string|null     $code
     * @param string|null     $title
     * @param string|null     $detail
     * @param string[]|null   $links
     * @param string[]|null   $paths
     * @param array|null      $members Array of additional members in [memberName => memberValue, ...] format
     *
     * @return ErrorInterface
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
    );
}
