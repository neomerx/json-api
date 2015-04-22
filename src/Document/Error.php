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

use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;

/**
 * @package Neomerx\JsonApi
 */
class Error implements ErrorInterface
{
    /**
     * @var int|string|null
     */
    private $idx;

    /**
     * @var string|null
     */
    private $href;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $detail;

    /**
     * @var string[]|null
     */
    private $paths;

    /**
     * @var string[]|null
     */
    private $links;

    /**
     * @var array|null
     */
    private $members;

    /**
     * @param int|string|null $idx
     * @param string|null     $href
     * @param string|null     $status
     * @param string|null     $code
     * @param string|null     $title
     * @param string|null     $detail
     * @param string[]|null   $links
     * @param string[]|null   $paths
     * @param array|null      $members Array of additional members in [memberName => memberValue, ...] format
     */
    public function __construct(
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
        assert('$idx === null     || is_int($idx) || is_string($idx)');
        assert('$href === null    || is_string($href)');
        assert('$status === null  || is_string($status)');
        assert('$code === null    || is_string($code)');
        assert('$title === null   || is_string($title)');
        assert('$title === null   || is_string($title)');
        assert('$detail === null  || is_string($detail)');
        assert('$links === null   || is_array($links)');
        assert('$paths === null   || is_array($paths)');
        assert('$members === null || is_array($members)');

        $this->idx     = $idx;
        $this->href    = $href;
        $this->status  = $status;
        $this->code    = $code;
        $this->title   = $title;
        $this->paths   = $paths;
        $this->links   = $links;
        $this->detail  = $detail;
        $this->members = $members;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idx;
    }

    /**
     * @inheritdoc
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalMembers()
    {
        return $this->members;
    }
}
