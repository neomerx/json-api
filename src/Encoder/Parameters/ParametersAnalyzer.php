<?php namespace Neomerx\JsonApi\Encoder\Parameters;

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

use Neomerx\JsonApi\Contracts\Document\DocumentInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\ParametersAnalyzerInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * @package Neomerx\JsonApi
 */
class ParametersAnalyzer implements ParametersAnalyzerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var EncodingParametersInterface
     */
    private $parameters;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $includePathsCache = [];

    /**
     * @var array
     */
    private $includeRelationshipsCache = [];

    /**
     * @param EncodingParametersInterface $parameters
     * @param ContainerInterface          $container
     */
    public function __construct(EncodingParametersInterface $parameters, ContainerInterface $container)
    {
        $this->container  = $container;
        $this->parameters = $parameters;
    }

    /**
     * @inheritdoc
     */
    public function getParameters(): EncodingParametersInterface
    {
        return $this->parameters;
    }

    /**
     * @inheritdoc
     */
    public function isPathIncluded(?string $path, string $type): bool
    {
        // check if it's in cache
        if (isset($this->includePathsCache[$type][$path]) === true) {
            return $this->includePathsCache[$type][$path];
        }

        $includePaths = $this->getIncludePathsByType($type);

        $result =
            $this->hasExactPathMatch($includePaths, $path) === true ||
            // RC4 spec changed requirements and intermediate paths should be included as well
            $this->hasMatchWithIncludedPaths($includePaths, $path) === true;

        $this->includePathsCache[$type][$path] = $result;

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function getIncludeRelationships(?string $path, string $type): array
    {
        // check if it's in cache
        if (isset($this->includeRelationshipsCache[$type][$path]) === true) {
            return $this->includeRelationshipsCache[$type][$path];
        }

        $includePaths  = $this->getIncludePathsByType($type);
        $pathBeginning = (string)$path;
        $pathLength    = strlen($pathBeginning);

        $result = [];
        foreach ($includePaths as $curPath) {
            if ($pathLength === 0) {
                $relationshipName = $this->getRelationshipNameForTopResource($curPath);
            } elseif (strpos($curPath, $pathBeginning . DocumentInterface::PATH_SEPARATOR) === 0) {
                $relationshipName = $this->getRelationshipNameForResource($curPath, $pathLength);
            } else {
                $relationshipName = null;
            }

            // add $relationshipName to $result if not yet there
            if ($relationshipName !== null && isset($result[$relationshipName]) === false) {
                $result[$relationshipName] = $relationshipName;
            }
        }

        $this->includeRelationshipsCache[$type][$path] = $result;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function hasSomeFields(string $type): bool
    {
        $hasSomeFields = $this->getParameters()->getFieldSet($type) !== [];

        return $hasSomeFields;
    }

    /**
     * If path has exact match with one of the 'include' paths.
     *
     * @param string[]    $paths
     * @param string|null $path
     *
     * @return bool
     */
    protected function hasExactPathMatch(array $paths, ?string $path): bool
    {
        $result = in_array($path, $paths, true);

        return $result;
    }

    /**
     * If path matches one of the included paths.
     *
     * @param string[]    $paths
     * @param string|null $path
     *
     * @return bool
     */
    protected function hasMatchWithIncludedPaths(array $paths, ?string $path): bool
    {
        $hasMatch = false;

        if ($path !== null) {
            foreach ($paths as $targetPath) {
                if (strpos($targetPath, $path . DocumentInterface::PATH_SEPARATOR) === 0) {
                    $hasMatch = true;
                    break;
                }
            }
        }

        return $hasMatch;
    }

    /**
     * @param string $type
     *
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    private function getIncludePathsByType(string $type): array
    {
        $includePaths = $this->getParameters()->getIncludePaths();

        // if include paths are set in params use them otherwise use default include paths from schema
        if ($includePaths !== null) {
            return $includePaths;
        }

        $schema    = $this->container->getSchemaByResourceType($type);
        $typePaths = $schema->getIncludePaths();

        return $typePaths;
    }

    /**
     * @param string $curPath
     *
     * @return string
     */
    private function getRelationshipNameForTopResource(string $curPath): string
    {
        $nextSeparatorPos = strpos($curPath, DocumentInterface::PATH_SEPARATOR);
        $relationshipName = $nextSeparatorPos === false ? $curPath : substr($curPath, 0, $nextSeparatorPos);

        return $relationshipName;
    }

    /**
     * @param string $curPath
     * @param int    $pathLength
     *
     * @return string
     */
    private function getRelationshipNameForResource(string $curPath, int $pathLength): string
    {
        $nextSeparatorPos = strpos($curPath, DocumentInterface::PATH_SEPARATOR, $pathLength + 1);
        $relationshipName = $nextSeparatorPos === false ?
            substr($curPath, $pathLength + 1) :
            substr($curPath, $pathLength + 1, $nextSeparatorPos - $pathLength - 1);

        return $relationshipName;
    }
}
