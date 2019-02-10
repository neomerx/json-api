<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Http\Headers;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Exceptions\InvalidArgumentException;

/**
 * @package Neomerx\JsonApi
 */
class HeaderParametersParser implements HeaderParametersParserInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function parseAcceptHeader(string $value): iterable
    {
        if (empty($value) === true) {
            throw new InvalidArgumentException('value');
        }

        $ranges = \preg_split("/,(?=([^\"]*\"[^\"]*\")*[^\"]*$)/", $value);
        $count  = \count($ranges);
        for ($idx = 0; $idx < $count; ++$idx) {
            $fields = \explode(';', $ranges[$idx]);

            if (\strpos($fields[0], '/') === false) {
                throw new InvalidArgumentException('mediaType');
            }

            list($type, $subType) = \explode('/', $fields[0], 2);
            list($parameters, $quality) = $this->parseQualityAndParameters($fields);

            $mediaType = $this->factory->createAcceptMediaType($idx, $type, $subType, $parameters, $quality);

            yield $mediaType;
        }
    }

    /**
     * @inheritdoc
     */
    public function parseContentTypeHeader(string $mediaType): MediaTypeInterface
    {
        $fields = \explode(';', $mediaType);

        if (\strpos($fields[0], '/') === false) {
            throw new InvalidArgumentException('mediaType');
        }

        list($type, $subType) = \explode('/', $fields[0], 2);

        $parameters = null;
        $count      = \count($fields);
        for ($idx = 1; $idx < $count; ++$idx) {
            $fieldValue = $fields[$idx];
            if (empty($fieldValue) === true) {
                continue;
            }

            if (\strpos($fieldValue, '=') === false) {
                throw new InvalidArgumentException('mediaType');
            }

            list($key, $value) = \explode('=', $fieldValue, 2);
            $parameters[\trim($key)] = \trim($value, ' "');
        }

        return $this->factory->createMediaType($type, $subType, $parameters);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    private function parseQualityAndParameters(array $fields): array
    {
        $quality     = 1;
        $qParamFound = false;
        $parameters  = null;

        $count = \count($fields);
        for ($idx = 1; $idx < $count; ++$idx) {
            $fieldValue = $fields[$idx];
            if (empty($fieldValue) === true) {
                continue;
            }

            if (\strpos($fieldValue, '=') === false) {
                throw new InvalidArgumentException('mediaType');
            }

            list($key, $value) = \explode('=', $fieldValue, 2);

            $key   = \trim($key);
            $value = \trim($value, ' "');

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
