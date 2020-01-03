<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi\Schemas;

/**
 * Copyright 2015-2020 info@neomerx.com
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

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\BaseSchema;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\Samples\JsonApi\Models\Site;

/**
 * @package Neomerx\Samples\JsonApi
 */
class SiteSchema extends BaseSchema
{
    /**
     * @var bool
     */
    public static $isShowCustomLinks = true;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'sites';
    }

    /**
     * @inheritdoc
     */
    public function getId($site): ?string
    {
        assert($site instanceof Site);

        return (string)$site->siteId;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($site, ContextInterface $context): iterable
    {
        assert($site instanceof Site);

        return [
            'name' => $site->name,
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($site, ContextInterface $context): iterable
    {
        assert($site instanceof Site);

        $links = static::$isShowCustomLinks === false ? [] : [
            'some-sublink'  => new Link(true, $this->getSelfSubUrl($site) . '/resource-sublink', false),
            'external-link' => new Link(false,'www.example.com', false),
        ];

        return [
            'posts' => [
                self::RELATIONSHIP_DATA          => $site->posts,
                self::RELATIONSHIP_LINKS         => $links,
                self::RELATIONSHIP_LINKS_SELF    => true,
                self::RELATIONSHIP_LINKS_RELATED => false,
            ],
        ];
    }
}
