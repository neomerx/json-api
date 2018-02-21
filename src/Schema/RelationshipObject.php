<?php namespace Neomerx\JsonApi\Schema;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use Closure;
use Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;
use Neomerx\JsonApi\Factories\Exceptions;

/**
 * @package Neomerx\JsonApi
 */
class RelationshipObject implements RelationshipObjectInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var object|array|null
     */
    private $data;

    /**
     * @var array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    private $links;

    /**
     * @var object|array|null|Closure
     */
    private $meta;

    /**
     * @var bool
     */
    private $isShowData;

    /**
     * @var bool
     */
    private $isRoot;

    /**
     * @var bool
     */
    private $isDataEvaluated = false;

    /**
     * @param string                                                        $name
     * @param object|array|null|Closure                                     $data
     * @param array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface> $links
     * @param object|array|null|Closure                                     $meta
     * @param bool                                                          $isShowData
     * @param bool                                                          $isRoot
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function __construct(
        $name,
        $data,
        array $links,
        $meta,
        $isShowData,
        $isRoot
    ) {
        is_bool($isRoot) === true ?: Exceptions::throwInvalidArgument('isRoot', $isRoot);
        is_bool($isShowData) === true ?: Exceptions::throwInvalidArgument('isShowData', $isShowData);
        $isOk = (($isRoot === false && is_string($name) === true) || ($isRoot === true && $name === null));
        $isOk ?: Exceptions::throwInvalidArgument('name', $name);

        $this->name       = $name;
        $this->links      = $links;
        $this->meta       = $meta;
        $this->isShowData = $isShowData;
        $this->isRoot     = $isRoot;

        $this->setData($data);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        if ($this->isDataEvaluated === false) {
            $this->isDataEvaluated = true;

            if ($this->data instanceof Closure) {
                /** @var Closure $data */
                $data = $this->data;
                $this->setData($data());
            }
        }

        assert(is_array($this->data) === true || is_object($this->data) === true || $this->data === null);

        return $this->data;
    }

    /**
     * @param object|array|null|Closure $data
     *
     * @return void
     */
    public function setData($data)
    {
        assert(is_array($data) === true || $data instanceof Closure || is_object($data) === true || $data === null);

        $this->data            = $data;
        $this->isDataEvaluated = false;
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
    public function getMeta()
    {
        if ($this->meta instanceof Closure) {
            $meta = $this->meta;
            $this->meta = $meta();
        }

        return $this->meta;
    }

    /**
     * @inheritdoc
     */
    public function isShowData()
    {
        return $this->isShowData;
    }

    /**
     * @inheritdoc
     */
    public function isRoot()
    {
        return $this->isRoot;
    }
}
