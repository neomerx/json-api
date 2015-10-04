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

use \Closure;
use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Schema\RelationshipObjectInterface;

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
     * @var mixed
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
     * @param mixed                                                         $meta
     * @param bool                                                          $isShowData
     * @param bool                                                          $isRoot
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
        $this->data       = $data;
        $this->links      = $links;
        $this->meta       = $meta;
        $this->isShowData = $isShowData;
        $this->isRoot     = $isRoot;
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
                $this->data = $data();
            }
        }

        return $this->data;
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
