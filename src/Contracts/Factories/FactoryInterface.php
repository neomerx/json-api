<?php namespace Neomerx\JsonApi\Contracts\Factories;

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

use \Psr\Log\LoggerAwareInterface as PSR3;

use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;

use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface as SchFI;
use \Neomerx\JsonApi\Contracts\Document\DocumentFactoryInterface as DFI;
use \Neomerx\JsonApi\Contracts\Encoder\Stack\StackFactoryInterface as StkFI;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserFactoryInterface as PrsFI;
use \Neomerx\JsonApi\Contracts\Encoder\Handlers\HandlerFactoryInterface as HFI;
use \Neomerx\JsonApi\Contracts\Http\Parameters\ParametersFactoryInterface as PrmFI;

/**
 * @package Neomerx\JsonApi
 */
interface FactoryInterface extends DFI, PrsFI, StkFI, HFI, PrmFI, SchFI, PSR3
{
    /**
     * Create encoder.
     *
     * @param ContainerInterface  $container
     * @param EncoderOptions|null $encoderOptions
     *
     * @return EncoderInterface
     */
    public function createEncoder(ContainerInterface $container, EncoderOptions $encoderOptions = null);

    /**
     * Create codec matcher.
     *
     * @return CodecMatcherInterface
     */
    public function createCodecMatcher();
}
