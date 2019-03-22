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
final class CustomParser extends Parser
{
    /** @var string Special value to be used in include paths */
    public const PATH_WILDCARD_ALL = '*';

    /**
     * @var array
     */
    private $cachedPathsResults = [];

    /**
     * @inheritdoc
     */
    public function isPathRequested(string $path): bool
    {
        if (\array_key_exists($path, $this->cachedPathsResults) === true) {
            return $this->cachedPathsResults[$path];
        }

        $normalizedPaths = $this->getNormalizedPaths();
        $result          =
            isset($normalizedPaths[$path]) ||
            isset($normalizedPaths[static::PATH_WILDCARD_ALL]) ||
            $this->doesMatchSubPath($path);

        $this->cachedPathsResults[$path] = $result;

        return $result;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function doesMatchSubPath(string $path): bool
    {
        $normalizedPaths = $this->getNormalizedPaths();
        $separator       = DocumentInterface::PATH_SEPARATOR;

        // check if any wildcard like a.*, a.b.* is requested
        $curPath = '';
        foreach (\explode($separator, $path) as $part) {
            $curPath     .= $part . $separator;
            $wildcardPath = $curPath . static::PATH_WILDCARD_ALL;
            if (isset($normalizedPaths[$wildcardPath])) {
                return true;
            }
        }

        return false;
    }
}
