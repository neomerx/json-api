<?php namespace Neomerx\JsonApi\Contracts\Responses;

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

use \Neomerx\JsonApi\Contracts\Parameters\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\JsonApi
 */
interface ResponsesInterface
{
    /**
     * Get response with or without content.
     *
     * @param int                               $statusCode
     * @param MediaTypeInterface                $mediaType
     * @param string|null                       $content
     * @param SupportedExtensionsInterface|null $supportedExtensions
     *
     * @return mixed
     */
    public function getResponse(
        $statusCode,
        MediaTypeInterface $mediaType,
        $content = null,
        SupportedExtensionsInterface $supportedExtensions = null
    );

    /**
     * Get 'created' response (HTTP code 201) with content.
     *
     * @param string                            $location
     * @param MediaTypeInterface                $mediaType
     * @param string|null                       $content
     * @param SupportedExtensionsInterface|null $supportedExtensions
     *
     * @return mixed
     */
    public function getCreatedResponse(
        $location,
        MediaTypeInterface $mediaType,
        $content,
        SupportedExtensionsInterface $supportedExtensions = null
    );
}
