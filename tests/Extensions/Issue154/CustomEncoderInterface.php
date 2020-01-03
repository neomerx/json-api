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

use Closure;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
interface CustomEncoderInterface extends EncoderInterface
{
    /**
     * @param string         $type
     * @param string|Closure $schema
     *
     * @return self
     */
    public function addSchema(string $type, $schema): self;
}
