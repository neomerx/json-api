<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue236;

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

use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use function array_key_exists;
use function explode;
use function strrpos;
use function substr;

/**
 * @package Neomerx\Tests\JsonApi
 */
final class SchemaFields
{
    /** @var string Path constant */
    private const PATH_SEPARATOR = DocumentInterface::PATH_SEPARATOR;

    /** @var string Path constant */
    private const FIELD_SEPARATOR = ',';

    /** @var array */
    private $fastRelationships;

    /** @var array */
    private $fastRelationshipLists;

    /** @var array */
    private $fastFields;

    /** @var array */
    private $fastFieldLists;

    /**
     * @param iterable $paths
     * @param iterable $fieldSets
     */
    public function __construct(iterable $paths, iterable $fieldSets)
    {
        foreach ($paths as $path) {
            $separatorPos = strrpos($path, static::PATH_SEPARATOR);
            if ($separatorPos === false) {
                $curPath      = '';
                $relationship = $path;
            } else {
                $curPath      = substr($path, 0, $separatorPos);
                $relationship = substr($path, $separatorPos + 1);
            }
            $this->fastRelationships[$curPath][$relationship] = true;
            $this->fastRelationshipLists[$curPath][]          = $relationship;
        }

        foreach ($fieldSets as $type => $fieldList) {
            foreach (explode(static::FIELD_SEPARATOR, $fieldList) as $field) {
                $this->fastFields[$type][$field] = true;
                $this->fastFieldLists[$type][]   = $field;
            }
        }
    }

    /**
     * @param string $currentPath
     * @param string $relationship
     *
     * @return bool
     */
    public function isRelationshipRequested(string $currentPath, string $relationship): bool
    {
        return isset($this->fastRelationships[$currentPath][$relationship]);
    }

    /**
     * @param string $currentPath
     *
     * @return array
     */
    public function getRequestedRelationships(string $currentPath): array
    {
        return $this->fastRelationshipLists[$currentPath] ?? [];
    }

    /**
     * @param string $type
     * @param string $field
     *
     * @return bool
     */
    public function isFieldRequested(string $type, string $field): bool
    {
        return array_key_exists($type, $this->fastFields) === false ? true : isset($this->fastFields[$type][$field]);
    }

    /**
     * @param string $type
     *
     * @return array|null
     */
    public function getRequestedFields(string $type): ?array
    {
        return $this->fastFieldLists[$type] ?? null;
    }
}
