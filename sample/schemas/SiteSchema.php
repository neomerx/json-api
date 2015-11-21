<?php

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

use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Schema\SchemaProvider;

/**
 * @package Neomerx\Samples\JsonApi
 */
class SiteSchema extends SchemaProvider
{
    protected $resourceType = 'sites';
    protected $selfSubUrl  = '/sites/';

    /**
     * @var bool
     */
    public static $isShowCustomLinks = true;

    public function getId($site)
    {
        /** @var Site $site */
        return $site->siteId;
    }

    public function getAttributes($site)
    {
        /** @var Site $site */
        return [
            'name' => $site->name,
        ];
    }

    public function getRelationships($site, array $includeList = [])
    {
        /** @var Site $site */

        $links = static::$isShowCustomLinks === false ? [] : [
            'some-sublink'  => new Link('resource-sublink'),
            'external-link' => new Link('www.example.com', null, true),
        ];

        return [
            'posts' => [
                self::DATA  => $site->posts,
                self::LINKS => $links,
            ],
        ];
    }

    public function getIncludePaths()
    {
        return [
            'posts',
            'posts.author',
            'posts.comments',
        ];
    }
}
