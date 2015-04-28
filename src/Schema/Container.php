<?php namespace Neomerx\JsonApi\Schema;

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

use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaProviderInterface;

/**
 * @package Neomerx\JsonApi
 */
class Container implements ContainerInterface
{
    /**
     * @var array
     */
    private $providerMapping = [];

    /**
     * @var array
     */
    private $createdProviders = [];

    /**
     * @var SchemaFactoryInterface
     */
    private $factory;

    /**
     * @param SchemaFactoryInterface $factory
     * @param array            $providers
     */
    public function __construct(SchemaFactoryInterface $factory, array $providers = [])
    {
        $this->factory = $factory;
        $this->registerArray($providers);
    }

    /**
     * Register provider for resource type.
     *
     * @param string $resourceType
     * @param string $providerClass
     *
     * @return void
     */
    public function register($resourceType, $providerClass)
    {
        assert('is_string($resourceType) && empty($resourceType) === false');
        assert('is_string($providerClass) && empty($providerClass) === false');
        assert('isset($this->providerMapping[$resourceType]) === false');

        $this->providerMapping[$resourceType] = $providerClass;
    }

    /**
     * Register providers for resource types.
     *
     * @param array $providers
     *
     * @return void
     */
    public function registerArray(array $providers)
    {
        foreach ($providers as $type => $className) {
            $this->register($type, $className);
        }
    }

    /**
     * @inheritdoc
     */
    public function getSchema($resource)
    {
        $resourceType = get_class($resource);

        if (isset($this->createdProviders[$resourceType])) {
            return $this->createdProviders[$resourceType];
        }

        assert('isset($this->providerMapping[$resourceType])', 'Have you added provider for `'.$resourceType.'`?');

        $className = $this->providerMapping[$resourceType];
        $this->createdProviders[$resourceType] = ($provider = new $className($this->factory, $this));

        assert('$provider instanceof '.SchemaProviderInterface::class);

        return $provider;
    }
}
