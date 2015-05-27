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

use \Neomerx\JsonApi\Schema\PaginationLinks;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface as Link;
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;

/**
 * @package Neomerx\JsonApi
 */
class DocumentLinks extends PaginationLinks implements DocumentLinksInterface
{
    /**
     * @var Link|null
     */
    private $selfUrl;

    /**
     * @param Link|null $self
     * @param Link|null $first
     * @param Link|null $last
     * @param Link|null $prev
     * @param Link|null $next
     */
    public function __construct(
        Link $self = null,
        Link $first = null,
        Link $last = null,
        Link $prev = null,
        Link $next = null
    ) {
        parent::__construct($first, $last, $prev, $next);
        $this->selfUrl = $self;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl()
    {
        return $this->selfUrl;
    }
}
