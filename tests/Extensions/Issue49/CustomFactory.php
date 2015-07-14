<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue49;

use \Neomerx\JsonApi\Factories\Factory;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\DataAnalyzerInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserManagerInterface;

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
 * @package Neomerx\Tests\JsonApi
 */
class CustomFactory extends Factory
{
    /**
     * @inheritdoc
     */
    public function createParser(DataAnalyzerInterface $analyzer, ParserManagerInterface $manager = null)
    {
        return new CustomParser($this, $this, $this, $analyzer, $manager);
    }
}
