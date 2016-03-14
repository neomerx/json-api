<?php namespace Neomerx\JsonApi\Contracts\Http;

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

use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\Exceptions\ErrorCollection;

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
     * @param object|array $data
     * @param int          $statusCode
     * @param array|null   $links
     * @param mixed        $meta
     *
     * @return mixed
     */
    public function getContentResponse($data, $statusCode = self::HTTP_OK, $links = null, $meta = null);

    /**
     * Get response for newly created resource with HTTP code 201 (adds 'location' header).
     *
     * @param object     $resource
     * @param array|null $links
     * @param mixed      $meta
     *
     * @return mixed
     */
    public function getCreatedResponse($resource, $links = null, $meta = null);


    /**
     * Get response with HTTP code only.
     *
     * @param $statusCode
     *
     * @return mixed
     */
    public function getCodeResponse($statusCode);

    /**
     * Get response with meta information only.
     *
     * @param array|object $meta       Meta information.
     * @param int          $statusCode
     *
     * @return mixed
     */
    public function getMetaResponse($meta, $statusCode = self::HTTP_OK);

    /**
     * Get response with JSON API Error in body.
     *
     * @param Error|Error[]|ErrorCollection $errors
     * @param int                           $statusCode
     *
     * @return mixed
     */
    public function getErrorResponse($errors, $statusCode = self::HTTP_BAD_REQUEST);
}
