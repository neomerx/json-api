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

use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * @package Neomerx\JsonApi
 */
class Link implements LinkInterface
{
    /**
     * @var string
     */
    private $subHref;

    /**
     * @var array|object|null
     */
    private $meta;

    /**
     * @var bool
     */
    private $treatAsHref;

    /**
     * @param string $subHref
     * @param mixed  $meta
     * @param bool   $treatAsHref If $subHref is a full URL and must not be concatenated with other URLs.
     */
    public function __construct($subHref, $meta = null, $treatAsHref = false)
    {
        is_string($subHref) === true ?: Exceptions::throwInvalidArgument('subHref', $subHref);
        is_bool($treatAsHref) === true ?: Exceptions::throwInvalidArgument('treatAsHref', $treatAsHref);

        $this->subHref     = $subHref;
        $this->meta        = $meta;
        $this->treatAsHref = $treatAsHref;
    }

    /**
     * @inheritdoc
     */
    public function getSubHref()
    {
        return $this->subHref;
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
    public function isTreatAsHref()
    {
        return $this->treatAsHref;
    }
}
