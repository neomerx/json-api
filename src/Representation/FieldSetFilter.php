<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Representation;

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

use Neomerx\JsonApi\Contracts\Parser\PositionInterface;
use Neomerx\JsonApi\Contracts\Parser\ResourceInterface;
use Neomerx\JsonApi\Contracts\Representation\FieldSetFilterInterface;

/**
 * @package Neomerx\JsonApi
 */
class FieldSetFilter implements FieldSetFilterInterface
{
    /**
     * @var array
     */
    private $fieldSets;

    /**
     * @param array|null $fieldSets
     */
    public function __construct(array $fieldSets)
    {
        $this->fieldSets = [];

        foreach ($fieldSets as $type => $fields) {
            \assert(\is_string($type) === true && empty($type) === false);
            \assert(\is_iterable($fields) === true);

            $this->fieldSets[$type] = [];

            foreach ($fields as $field) {
                \assert(\is_string($field) === true && empty($field) === false);
                \assert(isset($this->fieldSets[$type][$field]) === false);

                $this->fieldSets[$type][$field] = true;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getAttributes(ResourceInterface $resource): iterable
    {
        yield from $this->filterFields($resource->getType(), $resource->getAttributes());
    }

    /**
     * @inheritdoc
     */
    public function getRelationships(ResourceInterface $resource): iterable
    {
        yield from $this->filterFields($resource->getType(), $resource->getRelationships());
    }

    /**
     * @inheritdoc
     */
    public function shouldOutputRelationship(PositionInterface $position): bool
    {
        $parentType = $position->getParentType();
        if ($this->hasFilter($parentType) === true) {
            return isset($this->getAllowedFields($parentType)[$position->getParentRelationship()]);
        }

        return true;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function hasFilter(string $type): bool
    {
        return isset($this->fieldSets[$type]) === true;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    protected function getAllowedFields(string $type): array
    {
        \assert($this->hasFilter($type) === true);

        return $this->fieldSets[$type];
    }

    /**
     * @param string   $type
     * @param iterable $fields
     *
     * @return iterable
     */
    protected function filterFields(string $type, iterable $fields): iterable
    {
        if ($this->hasFilter($type) === false) {
            yield from $fields;

            return;
        }

        $allowedFields = $this->getAllowedFields($type);
        foreach ($fields as $name => $value) {
            if (isset($allowedFields[$name]) === true) {
                yield $name => $value;
            }
        }
    }
}
