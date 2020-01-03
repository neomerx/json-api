<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue154;

/**
 * Copyright 2015-2020 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Factories\Factory;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CustomFactory extends Factory
{
    /**
     * @inheritdoc
     */
    public function createSchemaContainer(iterable $schemas): SchemaContainerInterface
    {
        return new CustomSchemaContainer($this, $schemas);
    }

    /**
     * @inheritdoc
     */
    public function createEncoder(SchemaContainerInterface $container): EncoderInterface
    {
        return new CustomEncoder($this, $container);
    }
}
