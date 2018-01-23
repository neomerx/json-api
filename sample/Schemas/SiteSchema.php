<?php namespace Neomerx\Samples\JsonApi\Schemas;

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

use Neomerx\JsonApi\Document\Link;
use Neomerx\JsonApi\Schema\BaseSchema;
use Neomerx\Samples\JsonApi\Models\Site;

/**
 * @package Neomerx\Samples\JsonApi
 */
class SiteSchema extends BaseSchema
{
    /**
     * @inheritdoc
     */
    protected $resourceType = 'sites';

    /**
     * @var bool
     */
    public static $isShowCustomLinks = true;

    /**
     * @inheritdoc
     */
    public function getId($site): ?string
    {
        /** @var Site $site */
        return $site->siteId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($site, array $fieldKeysFilter = null): ?array
    {
        /** @var Site $site */
        return [
            'name' => $site->name,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($site, bool $isPrimary, array $includeRelationships): ?array
    {
        /** @var Site $site */

        $links = static::$isShowCustomLinks === false ? [] : [
            'some-sublink'  => new Link($this->getSelfSubUrl($site) . '/resource-sublink'),
            'external-link' => new Link('www.example.com', null, true),
        ];

        return [
            'posts' => [
                self::DATA  => $site->posts,
                self::LINKS => $links,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getIncludePaths(): array
    {
        return [
            'posts',
            'posts.author',
            'posts.comments',
        ];
    }
}
