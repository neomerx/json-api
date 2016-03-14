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
use \Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;

/**
 * @package Neomerx\JsonApi
 */
class AcceptMediaType extends MediaType implements AcceptMediaTypeInterface
{
    /**
     * @var float [0..1]
     */
    private $quality;

    /**
     * @var array<string,string>|null
     */
    private $extensions;

    /**
     * @var int
     */
    private $position;

    /**
     * @param int                       $position
     * @param string                    $type
     * @param string                    $subType
     * @param array<string,string>|null $parameters
     * @param float                     $quality
     * @param array<string,string>|null $extensions
     */
    public function __construct($position, $type, $subType, $parameters = null, $quality = 1.0, $extensions = null)
    {
        parent::__construct($type, $subType, $parameters);

        if (is_int($position) === false || $position < 0) {
            throw new InvalidArgumentException('position');
        }

        // rfc2616: 3 digits are meaningful (#3.9 Quality Values)
        $quality = floor((float)$quality * 1000) / 1000;
        if ($quality < 0 || $quality > 1) {
            throw new InvalidArgumentException('quality');
        }

        if ($extensions !== null && is_array($extensions) === false) {
            throw new InvalidArgumentException('extensions');
        }

        $this->position   = $position;
        $this->quality    = $quality;
        $this->extensions = $extensions;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * @inheritdoc
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @inheritdoc
     *
     * @return AcceptMediaTypeInterface
     */
    public static function parse($position, $mediaType)
    {
        $fields = explode(';', $mediaType);

        if (strpos($fields[0], '/') === false) {
            throw new InvalidArgumentException('mediaType');
        }

        list($type, $subType) = explode('/', $fields[0], 2);
        list($parameters, $quality, $extensions) = self::parseQualityAndParameters($fields);

        return new AcceptMediaType($position, $type, $subType, $parameters, $quality, $extensions);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private static function parseQualityAndParameters(array $fields)
    {
        $quality     = 1;
        $qParamFound = false;
        $parameters  = null;
        $extensions  = null;

        $count = count($fields);
        for ($idx = 1; $idx < $count; ++$idx) {
            if (empty($fields[$idx]) === true) {
                continue;
            }

            if (strpos($fields[$idx], '=') === false) {
                throw new InvalidArgumentException('mediaType');
            }

            list($key, $value) = explode('=', $fields[$idx], 2);

            $key   = trim($key);
            $value = trim($value, ' "');

            // 'q' param separates media parameters from extension parameters

            if ($key === 'q' && $qParamFound === false) {
                $quality     = (float)$value;
                $qParamFound = true;
                continue;
            }

            $qParamFound === false ? $parameters[$key] = $value : $extensions[$key] = $value;
        }

        return [$parameters, $quality, $extensions];
    }
}
