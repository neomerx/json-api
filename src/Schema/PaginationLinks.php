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
use \Neomerx\JsonApi\Contracts\Schema\PaginationLinksInterface;

/**
 * @package Neomerx\JsonApi
 */
class PaginationLinks implements PaginationLinksInterface
{
    /**
     * @var LinkInterface|null
     */
    private $firstUrl;

    /**
     * @var LinkInterface|null
     */
    private $lastUrl;

    /**
     * @var LinkInterface|null
     */
    private $prevUrl;

    /**
     * @var LinkInterface|null
     */
    private $nextUrl;

    /**
     * @param LinkInterface|null $firstUrl
     * @param LinkInterface|null $lastUrl
     * @param LinkInterface|null $prevUrl
     * @param LinkInterface|null $nextUrl
     */
    public function __construct(
        LinkInterface $firstUrl = null,
        LinkInterface $lastUrl = null,
        LinkInterface $prevUrl = null,
        LinkInterface $nextUrl = null
    ) {
        $this->firstUrl = $firstUrl;
        $this->lastUrl  = $lastUrl;
        $this->prevUrl  = $prevUrl;
        $this->nextUrl  = $nextUrl;
    }

    /**
     * @inheritdoc
     */
    public function getFirstUrl()
    {
        return $this->firstUrl;
    }

    /**
     * @inheritdoc
     */
    public function getLastUrl()
    {
        return $this->lastUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPrevUrl()
    {
        return $this->prevUrl;
    }

    /**
     * @inheritdoc
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }
}
