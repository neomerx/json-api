<?php namespace Neomerx\JsonApi\Http\Headers;

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

use \InvalidArgumentException;
use \Neomerx\JsonApi\Contracts\Http\Headers\HeaderInterface;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
class Header implements HeaderInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var MediaTypeInterface[]
     */
    private $mediaTypes;

    /**
     * @param string               $name
     * @param MediaTypeInterface[] $mediaTypes
     */
    public function __construct($name, $mediaTypes)
    {
        $name = trim($name);
        if (empty($name) === true) {
            throw new InvalidArgumentException('name');
        }

        if (is_array($mediaTypes) === false) {
            throw new InvalidArgumentException('mediaTypes');
        }

        $this->name       = $name;
        $this->mediaTypes = $mediaTypes;
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
    public function getMediaTypes()
    {
        return $this->mediaTypes;
    }

    /**
     * Parse header.
     *
     * @param string $header
     * @param string $name
     *
     * @return HeaderInterface
     */
    public static function parse($header, $name)
    {
        if (is_string($name) === false || empty($name) === true) {
            throw new InvalidArgumentException('header');
        }

        $mediaTypes = [];
        $ranges     = preg_split("/,(?=([^\"]*\"[^\"]*\")*[^\"]*$)/", $header);
        $count      = count($ranges);
        for ($idx = 0; $idx < $count; ++$idx) {
            $mediaTypes[] = static::parseMediaType($idx, $ranges[$idx]);
        }

        return static::newInstance($name, $mediaTypes);
    }

    /**
     * @param int    $position
     * @param string $mediaType
     *
     * @return MediaTypeInterface
     */
    protected static function parseMediaType($position, $mediaType)
    {
        return MediaType::parse($position, $mediaType);
    }

    /**
     * @param string               $name
     * @param MediaTypeInterface[] $mediaTypes
     *
     * @return Header
     */
    protected static function newInstance($name, $mediaTypes)
    {
        return new static($name, $mediaTypes);
    }
}
