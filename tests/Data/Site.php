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

use \stdClass;

/**
 * @package Neomerx\Tests\JsonApi
 */
class Site extends stdClass
{
    const ATTRIBUTE_ID   = 'site_id';
    const ATTRIBUTE_NAME = 'name';
    const LINK_POSTS     = 'posts';

    /**
     * @param string     $identity
     * @param string     $name
     * @param array|null $posts
     *
     * @return Site
     */
    public static function instance($identity, $name, array $posts = null)
    {
        $site = new self();

        $site->{self::ATTRIBUTE_ID}   = $identity;
        $site->{self::ATTRIBUTE_NAME} = $name;

        $posts === null ?: $site->{self::LINK_POSTS} = $posts;

        return $site;
    }
}
