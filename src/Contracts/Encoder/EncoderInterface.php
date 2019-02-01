<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Encoder;

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

use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkWithAliasesInterface;

/**
 * @package Neomerx\JsonApi
 */
interface EncoderInterface
{
    /** JSON API version implemented by the encoder */
    const JSON_API_VERSION = '1.1';

    /**
     * This prefix will be used for URL links while encoding.
     *
     * @param string $prefix
     *
     * @return self
     */
    public function withUrlPrefix(string $prefix): self;

    /**
     * Include specified paths to the output. Paths should be separated with a dot symbol.
     *
     * Format
     * [
     *     'relationship1',
     *     'relationship1.sub-relationship2',
     * ]
     *
     * @param iterable $paths
     *
     * @return self
     */
    public function withIncludedPaths(iterable $paths): self;

    /**
     * Limit fields in the output result.
     *
     * Format
     * [
     *     'type1' => ['attribute1', 'attribute2', 'relationship1', ...]
     *     'type2' => [] // no fields in output, only type and id.
     *
     *     // 'type3' is not on the list so all its attributes and relationships will be in output.
     * ]
     *
     * @param array $fieldSets
     *
     * @return self
     */
    public function withFieldSets(array $fieldSets): self;

    /**
     * Set JSON encode options.
     *
     * @link http://php.net/manual/en/function.json-encode.php
     *
     * @param int $options
     *
     * @return self
     */
    public function withEncodeOptions(int $options): self;

    /**
     * Set JSON encode depth.
     *
     * @link http://php.net/manual/en/function.json-encode.php
     *
     * @param int $depth
     *
     * @return self
     */
    public function withEncodeDepth(int $depth): self;

    /**
     * Add links that will be encoded with data. Links must be in `$name => $link, ...` format.
     *
     * @param array $links
     *
     * @see LinkInterface
     *
     * @return self
     */
    public function withLinks(array $links): self;

    /**
     * Add profile links that will be encoded with data. Links must be in `$link1, $link2, ...` format.
     *
     * @param iterable $links
     *
     * @see LinkWithAliasesInterface
     *
     * @return self
     */
    public function withProfile(iterable $links): self;

    /**
     * Add meta information that will be encoded with data. If 'null' meta will not appear in a document.
     *
     * @param mixed|null $meta
     *
     * @return self
     */
    public function withMeta($meta): self;

    /**
     * If called JSON API version information will be added to a document.
     *
     * @param string $version
     *
     * @return self
     *
     * @see http://jsonapi.org/format/#document-jsonapi-object
     */
    public function withJsonApiVersion(string $version): self;

    /**
     * If called JSON API version meta will be added to a document.
     *
     * @param mixed $meta
     *
     * @return self
     *
     * @see http://jsonapi.org/format/#document-jsonapi-object
     */
    public function withJsonApiMeta($meta): self;

    /**
     * Add 'self' Link to top-level document's 'links' section for relationship specified.
     *
     * @param object $resource
     * @param string $relationshipName
     *
     * @see http://jsonapi.org/format/#fetching-relationships
     *
     * @return self
     */
    public function withRelationshipSelfLink($resource, string $relationshipName): self;

    /**
     * Add 'related' Link to top-level document's 'links' section for relationship specified.
     *
     * @param object $resource
     * @param string $relationshipName
     *
     * @see http://jsonapi.org/format/#fetching-relationships
     *
     * @return self
     */
    public function withRelationshipRelatedLink($resource, string $relationshipName): self;

    /**
     * Reset encoder settings to defaults.
     *
     * @return self
     */
    public function reset(): self;

    /**
     * Encode input as JSON API string.
     *
     * @param object|iterable|null $data Data to encode.
     *
     * @return string
     */
    public function encodeData($data): string;

    /**
     * Encode input as JSON API string with a list of resource identifiers.
     *
     * @param object|iterable|null $data Data to encode.
     *
     * @return string
     */
    public function encodeIdentifiers($data): string;

    /**
     * Encode error as JSON API string.
     *
     * @param ErrorInterface $error
     *
     * @return string
     */
    public function encodeError(ErrorInterface $error): string;

    /**
     * Encode errors as JSON API string.
     *
     * @see ErrorInterface
     *
     * @param iterable $errors
     *
     * @return string
     */
    public function encodeErrors(iterable $errors): string;

    /**
     * Encode input meta as JSON API string.
     *
     * @param mixed $meta Meta information.
     *
     * @return string
     */
    public function encodeMeta($meta): string;
}
