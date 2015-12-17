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

use \Neomerx\JsonApi\Factories\Exceptions;
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
     * @var array|null
     */
    private $source;

    /**
     * @var mixed|null
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
     * @param mixed|null         $meta
     */
    public function __construct(
        $idx = null,
        LinkInterface $aboutLink = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        array $source = null,
        $meta = null
    ) {
        $this->checkIdx($idx);
        $this->checkCode($code);
        $this->checkTitle($title);
        $this->checkStatus($status);
        $this->checkDetail($detail);

        $this->idx     = $idx;
        $this->links   = ($aboutLink === null ? null : [DocumentInterface::KEYWORD_ERRORS_ABOUT => $aboutLink]);
        $this->status  = ($status !== null ? (string)$status : null);
        $this->code    = ($code !== null ? (string)$code : null);
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

    /**
     * @param int|string|null $idx
     */
    private function checkIdx($idx)
    {
        ($idx === null || is_int($idx) === true ||
            is_string($idx) === true) ?: Exceptions::throwInvalidArgument('idx', $idx);
    }

    /**
     * @param string|null $title
     */
    private function checkTitle($title)
    {
        ($title === null || is_string($title) === true) ?: Exceptions::throwInvalidArgument('title', $title);
    }

    /**
     * @param string|null $detail
     */
    private function checkDetail($detail)
    {
        ($detail === null || is_string($detail) === true) ?: Exceptions::throwInvalidArgument('detail', $detail);
    }

    /**
     * @param int|string|null $status
     */
    private function checkStatus($status)
    {
        $isOk = ($status === null || is_int($status) === true || is_string($status) === true);
        $isOk ?: Exceptions::throwInvalidArgument('status', $status);
    }

    /**
     * @param int|string|null $code
     */
    private function checkCode($code)
    {
        $isOk = ($code === null || is_int($code) === true || is_string($code) === true);
        $isOk ?: Exceptions::throwInvalidArgument('code', $code);
    }
}
