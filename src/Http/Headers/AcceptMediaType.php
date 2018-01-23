<?php namespace Neomerx\JsonApi\Http\Headers;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use InvalidArgumentException;
use Neomerx\JsonApi\Contracts\Http\Headers\AcceptMediaTypeInterface;

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
     * @var int
     */
    private $position;

    /**
     * @param int    $position
     * @param string $type
     * @param string $subType
     * @param array<string,string>|null $parameters
     * @param float  $quality
     */
    public function __construct(
        int $position,
        string $type,
        string $subType,
        array $parameters = null,
        float $quality = 1.0
    ) {
        parent::__construct($type, $subType, $parameters);

        if ($position < 0) {
            throw new InvalidArgumentException('position');
        }

        // rfc2616: 3 digits are meaningful (#3.9 Quality Values)
        $quality = floor($quality * 1000) / 1000;
        if ($quality < 0 || $quality > 1) {
            throw new InvalidArgumentException('quality');
        }

        $this->position = $position;
        $this->quality  = $quality;
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function getQuality(): float
    {
        return $this->quality;
    }

    /**
     * @inheritdoc
     *
     * @return AcceptMediaTypeInterface
     */
    public static function parse(int $position, string $mediaType)
    {
        $fields = explode(';', $mediaType);

        if (strpos($fields[0], '/') === false) {
            throw new InvalidArgumentException('mediaType');
        }

        list($type, $subType) = explode('/', $fields[0], 2);
        list($parameters, $quality) = self::parseQualityAndParameters($fields);

        return new AcceptMediaType($position, $type, $subType, $parameters, $quality);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private static function parseQualityAndParameters(array $fields): array
    {
        $quality     = 1;
        $qParamFound = false;
        $parameters  = null;

        $count = count($fields);
        for ($idx = 1; $idx < $count; ++$idx) {
            $fieldValue = $fields[$idx];
            if (empty($fieldValue) === true) {
                continue;
            }

            if (strpos($fieldValue, '=') === false) {
                throw new InvalidArgumentException('mediaType');
            }

            list($key, $value) = explode('=', $fieldValue, 2);

            $key   = trim($key);
            $value = trim($value, ' "');

            // 'q' param separates media parameters from extension parameters

            if ($key === 'q' && $qParamFound === false) {
                $quality     = (float)$value;
                $qParamFound = true;
                continue;
            }

            if ($qParamFound === false) {
                $parameters[$key] = $value;
            }
        }

        return [$parameters, $quality];
    }
}
