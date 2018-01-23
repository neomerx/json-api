<?php namespace Neomerx\JsonApi\Encoder\Serialize;

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

use Iterator;
use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface as CI;
use Neomerx\JsonApi\Exceptions\ErrorCollection;

/**
 * @method CI getContainer()
 * @method array encodeDataToArray(CI $container, $data, EncodingParametersInterface $parameters = null)
 * @method array encodeIdentifiersToArray($data, EncodingParametersInterface $parameters = null)
 * @method array encodeErrorToArray(ErrorInterface $error)
 * @method array encodeErrorsToArray($errors)
 * @method array encodeMetaToArray($meta)
 *
 * @package Neomerx\JsonApi
 */
trait ArraySerializerTrait
{
    /**
     * @param object|array|Iterator|null       $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return array
     */
    public function serializeData($data, EncodingParametersInterface $parameters = null): array
    {
        return $this->encodeDataToArray($this->getContainer(), $data, $parameters);
    }

    /**
     * @param object|array|Iterator|null       $data
     * @param EncodingParametersInterface|null $parameters
     *
     * @return array
     */
    public function serializeIdentifiers($data, EncodingParametersInterface $parameters = null): array
    {
        return $this->encodeIdentifiersToArray($data, $parameters);
    }

    /**
     * @param ErrorInterface $error
     *
     * @return array
     */
    public function serializeError(ErrorInterface $error): array
    {
        return $this->encodeErrorToArray($error);
    }

    /**
     * @param ErrorInterface[]|ErrorCollection $errors
     *
     * @return array
     */
    public function serializeErrors($errors): array
    {
        return $this->encodeErrorsToArray($errors);
    }

    /**
     * @param array|object $meta
     *
     * @return array
     */
    public function serializeMeta($meta): array
    {
        return $this->encodeMetaToArray($meta);
    }
}
