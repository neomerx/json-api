<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Extensions\Issue231;

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
use Neomerx\JsonApi\Parser\Parser;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CustomParser extends Parser
{
    /**
     * @inheritdoc
     */
    public function isPathRequested(string $path): bool
    {
        $normalizedPaths = $this->getNormalizedPaths();

        if (isset($normalizedPaths[$path])) {
            return true;
        }

        $wildcard = '*';
        if (isset($normalizedPaths[$wildcard])) {
            return true;
        }

        $separator = DocumentInterface::PATH_SEPARATOR;
        $testPath = '';

        // check if any wildcard like a.*, a.b.* is requested
        foreach (\explode($separator, $path) as $part) {
            $testPath .= $part . $separator;
            $wildcardPath = $testPath . $wildcard;
            if (isset($normalizedPaths[$wildcardPath])) {
                return true;
            }
        }

        return false;
    }
}
