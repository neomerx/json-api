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

use \Neomerx\JsonApi\Contracts\Document\LinkInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class CommentSchema extends DevSchemaProvider
{
    /**
     * @inheritdoc
     */
    protected $resourceType = 'comments';

    /**
     * @inheritdoc
     */
    public function getId($comment)
    {
        return $comment->{Comment::ATTRIBUTE_ID};
    }

    /**
     * @inheritdoc
     */
    public function getAttributes($comment)
    {
        return [
            Comment::ATTRIBUTE_BODY => $comment->{Comment::ATTRIBUTE_BODY},
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRelationships($comment, $isPrimary, array $includeRelationships)
    {
        assert('$comment instanceof '.Comment::class);

        if (isset($includeRelationships[Comment::LINK_AUTHOR]) === true) {
            $data = $comment->{Comment::LINK_AUTHOR};
        } else {
            // issue #75 https://github.com/neomerx/json-api/issues/75
            // as author will not be included as full resource let's replace it with just identity (type + id)
            /** @var Author $author */
            $author         = $comment->{Comment::LINK_AUTHOR};
            $authorId       = $author->{Author::ATTRIBUTE_ID};
            $authorIdentity = Author::instance($authorId, null, null);

            $data = $authorIdentity;
        }

        if (($isPrimary && $this->isIsLinksInPrimary()) || (!$isPrimary && $this->isIsLinksInIncluded())) {
            $selfLink = $this->getRelationshipSelfLink($comment, Comment::LINK_AUTHOR);
            $links    = [
                Comment::LINK_AUTHOR => [self::LINKS => [LinkInterface::SELF => $selfLink], self::SHOW_DATA => false],
            ];
        } else {
            $links = [
                Comment::LINK_AUTHOR => [self::DATA => $data],
            ];
        }

        // NOTE: The line(s) below for testing purposes only. Not for production.
        $this->fixLinks($comment, $links);

        return $links;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedResourceLinks($resource)
    {
        $links = [
            LinkInterface::SELF => $this->getSelfSubLink($resource),
        ];

        return $links;
    }
}
