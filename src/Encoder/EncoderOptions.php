<?php namespace Neomerx\JsonApi\Encoder;

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

/**
 * @package Neomerx\JsonApi
 */
class EncoderOptions
{
    /**
     * @var int
     */
    private $options;

    /**
     * @var int
     */
    private $depth;

    /**
     * @var null|string
     */
    private $urlPrefix;

    /**
     * @param int         $options
     * @param string|null $urlPrefix
     * @param int         $depth
     */
    public function __construct(
        $options = 0,
        $urlPrefix = null,
        $depth = 512
    ) {
        is_int($depth) === true ?: Exceptions::throwInvalidArgument('depth', $depth);
        is_int($options) === true ?: Exceptions::throwInvalidArgument('options', $options);

        $isOk = ($urlPrefix === null || is_string($urlPrefix) === true);
        $isOk ?: Exceptions::throwInvalidArgument('urlPrefix', $urlPrefix);

        $this->options   = $options;
        $this->depth     = $depth;
        $this->urlPrefix = $urlPrefix;
    }

    /**
     * @link http://php.net/manual/en/function.json-encode.php
     *
     * @return int
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @link http://php.net/manual/en/function.json-encode.php
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * @return null|string
     */
    public function getUrlPrefix()
    {
        return $this->urlPrefix;
    }
}
