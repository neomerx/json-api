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
use \Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use \Neomerx\JsonApi\Contracts\Document\DocumentInterface;

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
     * @var null|array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    private $links;

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
     * @var mixed|null
     */
    private $source;

    /**
     * @var array|null
     */
    private $meta;

    /**
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param int|string|null    $status
     * @param int|string|null    $code
     * @param string|null        $title
     * @param string|null        $detail
     * @param array|null         $source
     * @param array|null         $meta
     */
    public function __construct(
        $idx = null,
        LinkInterface $aboutLink = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        array $source = null,
        array $meta = null
    ) {
        assert(
            '($idx === null || is_int($idx) || is_string($idx)) &&'.
            '($status === null || is_int($status) || is_string($status)) &&'.
            '($code === null || is_int($code) || is_string($code)) &&'.
            '($title === null  || is_string($title)) && ($title === null || is_string($title)) &&'.
            '($detail === null || is_string($detail)) && ($meta === null || is_array($meta))'
        );

        $this->idx     = $idx;
        $this->links   = ($aboutLink === null ? null : [DocumentInterface::KEYWORD_ERRORS_ABOUT => $aboutLink]);
        $this->status  = (string) $status;
        $this->code    = (string) $code;
        $this->title   = $title;
        $this->source  = $source;
        $this->detail  = $detail;
        $this->meta    = $meta;
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
    public function getLinks()
    {
        return $this->links;
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
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
