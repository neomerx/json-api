<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Representation;

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

/**
 * @package Neomerx\JsonApi
 */
interface BaseWriterInterface
{
    /**
     * Get final document.
     *
     * @return array
     */
    public function getDocument(): array;

    /**
     * Main data section will be an array of resources or identifiers.
     *
     * @return self
     */
    public function setDataAsArray(): self;

    /**
     * @param mixed $meta
     *
     * @return self
     */
    public function setMeta($meta): self;

    /**
     * @param string $version
     *
     * @return self
     */
    public function setJsonApiVersion(string $version): self;

    /**
     * @param mixed $meta
     *
     * @return self
     */
    public function setJsonApiMeta($meta): self;

    /**
     * @param string $prefix
     *
     * @return self
     */
    public function setUrlPrefix(string $prefix): self;

    /**
     * @param iterable $links
     *
     * @return self
     */
    public function setLinks(iterable $links): self;

    /**
     * @param iterable $links
     *
     * @return self
     */
    public function setProfile(iterable $links): self;
}
