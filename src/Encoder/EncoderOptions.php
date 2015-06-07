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
     * @var bool
     */
    private $isShowVersionInfo;

    /**
     * @var mixed|null
     */
    private $versionMeta;

    /**
     * @var null|string
     */
    private $urlPrefix;

    /**
     * @param int    $options
     * @param string $urlPrefix
     * @param bool   $isShowVersionInfo
     * @param mixed  $versionMeta
     * @param int    $depth
     */
    public function __construct(
        $options = 0,
        $urlPrefix = null,
        $isShowVersionInfo = false,
        $versionMeta = null,
        $depth = 512
    ) {
        assert(
            'is_int($options) && is_int($depth) && ($urlPrefix === null || is_string($urlPrefix)) &&'.
            ' is_bool($isShowVersionInfo)'
        );

        $this->options           = $options;
        $this->depth             = $depth;
        $this->urlPrefix         = $urlPrefix;
        $this->isShowVersionInfo = $isShowVersionInfo;
        $this->versionMeta       = $versionMeta;
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
     * If JSON API version should be rendered on document top level.
     *
     * @return bool
     */
    public function isShowVersionInfo()
    {
        return $this->isShowVersionInfo;
    }

    /**
     * Get JSON API meta information for version.
     *
     * @link http://jsonapi.org/format/#document-jsonapi-object
     *
     * @return mixed|null
     */
    public function getVersionMeta()
    {
        return $this->versionMeta;
    }

    /**
     * @return null|string
     */
    public function getUrlPrefix()
    {
        return $this->urlPrefix;
    }
}
