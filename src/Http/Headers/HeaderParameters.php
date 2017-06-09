<?php namespace Neomerx\JsonApi\Http\Headers;

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

use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptHeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
class HeaderParameters implements HeaderParametersInterface
{
    /**
     * @var string
     */
    private $method;

    /**
     * @var HeaderInterface|null
     */
    private $contentType;

    /**
     * @var AcceptHeaderInterface
     */
    private $accept;

    /**
     * @param string                $method
     * @param AcceptHeaderInterface $accept
     * @param HeaderInterface|null  $contentType
     */
    public function __construct($method, AcceptHeaderInterface $accept, HeaderInterface $contentType = null)
    {
        $this->accept      = $accept;
        $this->contentType = $contentType;
        $this->method      = $method;
    }

    /**
     * @inheritdoc
     */
    public function getContentTypeHeader()
    {
        return $this->contentType;
    }

    /**
     * @inheritdoc
     */
    public function getAcceptHeader()
    {
        return $this->accept;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }
}
