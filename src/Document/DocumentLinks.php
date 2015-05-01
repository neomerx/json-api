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
use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;

/**
 * @package Neomerx\JsonApi
 */
class DocumentLinks extends PaginationLinks implements DocumentLinksInterface
{
    /**
     * @var string|null
     */
    private $selfUrl;

    /**
     * @param string|null $selfUrl
     * @param string|null $firstUrl
     * @param string|null $lastUrl
     * @param string|null $prevUrl
     * @param string|null $nextUrl
     */
    public function __construct($selfUrl = null, $firstUrl = null, $lastUrl = null, $prevUrl = null, $nextUrl = null)
    {
        assert('$selfUrl  === null || is_string($selfUrl)');

        parent::__construct($firstUrl, $lastUrl, $prevUrl, $nextUrl);
        $this->selfUrl = $selfUrl;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl()
    {
        return $this->selfUrl;
    }
}
