<?php namespace Neomerx\Tests\JsonApi\Data;

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

/**
 * @package Neomerx\Tests\JsonApi
 */
class DummySchema extends DevSchemaProvider
{
    /**
     * @inheritdoc
     */
    protected $resourceType = 'dummy';

    /**
     * @inheritdoc
     */
    protected $baseSelfUrl = 'dummyUrl';

    /**
     * @inheritdoc
     */
    public function getId($resource)
    {
        return 'dummyId';
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($author)
    {
        return [];
    }
}
