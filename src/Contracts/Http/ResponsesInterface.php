<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Contracts\Http;

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

/**
 * @package Neomerx\JsonApi
 */
interface ResponsesInterface
{
    /**
     * HTTP code constant.
     */
    const HTTP_OK = 200;

    /**
     * HTTP code constant.
     */
    const HTTP_CREATED = 201;

    /**
     * HTTP code constant.
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * Get response with regular JSON API Document in body.
     *
     * @param object|array $data       Resource or resources to encode.
     * @param int          $statusCode HTTP status code.
     * @param array        $headers    Additional headers.
     *
     * @return mixed
     */
    public function getContentResponse($data, int $statusCode = self::HTTP_OK, array $headers = []);

    /**
     * Get response for newly created resource with HTTP code 201 (adds 'location' header).
     *
     * @param object $resource Newly created resource to encode.
     * @param string $url      URL of the resource.
     * @param array  $headers  Additional headers.
     *
     * @return mixed
     */
    public function getCreatedResponse($resource, string $url, array $headers = []);

    /**
     * Get response with HTTP code only.
     *
     * @param int   $statusCode HTTP status code.
     * @param array $headers    Additional headers.
     *
     * @return mixed
     */
    public function getCodeResponse(int $statusCode, array $headers = []);

    /**
     * Get response with meta information only.
     *
     * @param array|object $meta       Meta information.
     * @param int          $statusCode HTTP status code.
     * @param array        $headers    Additional headers.
     *
     * @return mixed
     */
    public function getMetaResponse($meta, int $statusCode = self::HTTP_OK, array $headers = []);

    /**
     * Get response with only resource identifiers.
     *
     * @param object|array $data       Resource or resources to encode.
     * @param int          $statusCode HTTP status code.
     * @param array        $headers    Additional headers.
     *
     * @return mixed
     */
    public function getIdentifiersResponse($data, int $statusCode = self::HTTP_OK, array $headers = []);

    /**
     * Get response with JSON API Error in body.
     *
     * @param ErrorInterface|iterable $errors     Error or errors to encode.
     * @param int                     $statusCode HTTP status code.
     * @param array                   $headers    Additional headers.
     *
     * @return mixed
     */
    public function getErrorResponse($errors, int $statusCode = self::HTTP_BAD_REQUEST, array $headers = []);
}
