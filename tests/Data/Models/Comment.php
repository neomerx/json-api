<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Data\Models;

/**
 * Copyright 2015-2019 info@neomerx.com
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

use stdClass;

/**
 * @package Neomerx\Tests\JsonApi
 */
class Comment extends stdClass
{
    const ATTRIBUTE_ID = 'comment_id';
    const ATTRIBUTE_BODY = 'body';
    const LINK_AUTHOR = 'author';

    /**
     * @param int    $identity
     * @param string $body
     * @param Author $author
     *
     * @return Comment
     */
    public static function instance(int $identity, string $body, Author $author = null)
    {
        $comment = new self();

        $comment->{self::ATTRIBUTE_ID}   = $identity;
        $comment->{self::ATTRIBUTE_BODY} = $body;

        $comment->{self::LINK_AUTHOR} = $author;

        return $comment;
    }
}
