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

use \Neomerx\JsonApi\Contracts\Document\DocumentLinksInterface;

/**
 * @package Neomerx\JsonApi
 */
class DocumentLinks implements DocumentLinksInterface
{
    /**
     * @var string|null
     */
    private $firstUrl;

    /**
     * @var string|null
     */
    private $lastUrl;

    /**
     * @var string|null
     */
    private $prevUrl;

    /**
     * @var string|null
     */
    private $nextUrl;

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
        assert('$firstUrl === null || is_string($firstUrl)');
        assert('$lastUrl  === null || is_string($lastUrl)');
        assert('$prevUrl  === null || is_string($prevUrl)');
        assert('$nextUrl  === null || is_string($nextUrl)');

        $this->selfUrl  = $selfUrl;
        $this->firstUrl = $firstUrl;
        $this->lastUrl  = $lastUrl;
        $this->prevUrl  = $prevUrl;
        $this->nextUrl  = $nextUrl;
    }

    /**
     * @inheritdoc
     */
    public function getSelfUrl()
    {
        return $this->selfUrl;
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
