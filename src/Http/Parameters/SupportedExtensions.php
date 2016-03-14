<?php namespace Neomerx\JsonApi\Http\Parameters;

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

use \Neomerx\JsonApi\Factories\Exceptions;
use \Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use \Neomerx\JsonApi\Contracts\Http\Parameters\SupportedExtensionsInterface;

/**
 * @package Neomerx\JsonApi
 */
class SupportedExtensions implements SupportedExtensionsInterface
{
    /**
     * @var string
     */
    private $extensions;

    /**
     * @param string $extensions
     */
    public function __construct($extensions = MediaTypeInterface::NO_EXT)
    {
        $this->setExtensions($extensions);
    }

    /**
     * @inheritdoc
     */
    public function setExtensions($extensions)
    {
        is_string($extensions) === true ?: Exceptions::throwInvalidArgument('extensions', $extensions);

        $this->extensions = $extensions;
    }

    /**
     * @inheritdoc
     */
    public function getExtensions()
    {
        return $this->extensions;
    }
}
