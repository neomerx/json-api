<?php namespace Neomerx\JsonApi\Encoder\Parser;

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

use \Iterator;
use \InvalidArgumentException;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\DataAnalyzerInterface;

/**
 * @package Neomerx\JsonApi
 */
class DataAnalyzer implements DataAnalyzerInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function analyze($data)
    {
        $isCollection    = true;
        $schema          = null;
        $traversableData = null;
        $firstItem       = null;

        if (is_array($data) === true) {
            /** @var array $data */
            $isEmpty = empty($data);
            $traversableData = $data;
            if ($isEmpty === false) {
                $firstItem = reset($data);
            }
        } elseif ($data instanceof Iterator) {
            /** @var Iterator $data */
            $data->rewind();
            $isEmpty = ($data->valid() === false);
            if ($isEmpty === false) {
                $firstItem       = $data->current();
                $traversableData = $data;
            }
        } elseif (is_object($data) === true) {
            /** @var object $data */
            $isEmpty         = ($data === null);
            $isCollection    = false;
            $firstItem       = $data;
            $traversableData = [$data];
        } elseif ($data === null) {
            $isCollection = false;
            $isEmpty      = true;
        } else {
            throw new InvalidArgumentException('data');
        }

        if ($firstItem !== null) {
            $schema = $this->container->getSchema($firstItem);
        }

        return [$isEmpty, $isCollection, $schema, $traversableData];
    }
}
