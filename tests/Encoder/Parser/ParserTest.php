<?php namespace Neomerx\Tests\JsonApi\Encoder\Parser;

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

use \Neomerx\Tests\JsonApi\Data\Post;
use \Neomerx\JsonApi\Schema\Container;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\JsonApi\Schema\SchemaFactory;
use \Neomerx\JsonApi\Encoder\Factory\EncoderFactory;
use \Neomerx\Tests\JsonApi\Data\AuthorSchemaWithComments;
use \Neomerx\Tests\JsonApi\Data\CommentSchemaWithAuthors;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserInterface;
use \Neomerx\Tests\JsonApi\Data\PostSchemaCommentsAsReference;
use \Neomerx\JsonApi\Contracts\Encoder\Parser\ParserReplyInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ParserTest extends BaseTestCase
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var Author
     */
    private $author;

    /**
     * @var Comment[]
     */
    private $comments;

    /**
     * @var Post
     */
    private $post;

    /**
     * @var array
     */
    private $authorAttr;

    /**
     * @var array
     */
    private $commAttr5;

    /**
     * @var array
     */
    private $commAttr12;

    /**
     * @var array
     */
    private $postAttr;

    /**
     * Set up tests.
     */
    protected function setUp()
    {
        parent::setUp();

        $schemas = [
            Author::class  => AuthorSchemaWithComments::class,
            Comment::class => CommentSchemaWithAuthors::class,
            Post::class    => PostSchemaCommentsAsReference::class,
        ];
        $container    = new Container(new SchemaFactory(), $schemas);
        $this->parser = (new EncoderFactory())->createParser($container);

        $this->author   = Author::instance(9, 'Dan', 'Gebhardt');
        $this->comments = [
            Comment::instance(5, 'First!', $this->author),
            Comment::instance(12, 'I like XML better', $this->author),
        ];
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $this->post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            $this->comments
        );

        $this->authorAttr = [
            Author::ATTRIBUTE_FIRST_NAME => $this->author->{Author::ATTRIBUTE_FIRST_NAME},
            Author::ATTRIBUTE_LAST_NAME  => $this->author->{Author::ATTRIBUTE_LAST_NAME},
        ];

        $this->commAttr5 = [
            Comment::ATTRIBUTE_BODY => $this->comments[0]->{Comment::ATTRIBUTE_BODY},
        ];

        $this->commAttr12 = [
            Comment::ATTRIBUTE_BODY => $this->comments[1]->{Comment::ATTRIBUTE_BODY},
        ];

        $this->postAttr = [
            Post::ATTRIBUTE_TITLE => $this->post->{Post::ATTRIBUTE_TITLE},
            Post::ATTRIBUTE_BODY  => $this->post->{Post::ATTRIBUTE_BODY},
        ];
    }

    /**
     * Test parse simple object with a reference to null.
     */
    public function testParseSimpleObjectWithNullLink()
    {
        $this->author->{Author::LINK_COMMENTS} = null;

        $start     = ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED;
        $startNull = ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED;
        $complete  = ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED;
        $expected  = [
            //           level link name   type      id    attributes         meta
            [$start,     1,    '',         'people', 9,    $this->authorAttr, null],
            [$startNull, 2,    'comments', null,     null, null,              null],
            [$complete,  1,    '',         'people', 9,    $this->authorAttr, null],
        ];

        $allReplies = [];
        foreach ($this->parser->parse($this->author) as $reply) {
            /** @var ParserReplyInterface $reply */
            $allReplies[] = $this->replyToArray($reply);
        }
        $this->assertEquals($expected, $allReplies);
    }

    /**
     * Test parse simple object with a reference to empty array.
     */
    public function testParseSimpleObjectWithEmptyArrayLink()
    {
        $this->author->{Author::LINK_COMMENTS} = [];

        $start      = ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED;
        $startEmpty = ParserReplyInterface::REPLY_TYPE_EMPTY_RESOURCE_STARTED;
        $complete   = ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED;
        $expected   = [
            //             level link name   type      id    attributes         meta
            [$start,       1,    '',         'people', 9,    $this->authorAttr, null],
            [$startEmpty,  2,    'comments', null,     null, null,              null],
            [$complete,    1,    '',         'people', 9,    $this->authorAttr, null],
        ];

        $allReplies = [];
        foreach ($this->parser->parse($this->author) as $reply) {
            /** @var ParserReplyInterface $reply */
            $allReplies[] = $this->replyToArray($reply);
        }
        $this->assertEquals($expected, $allReplies);
    }

    /**
     * Test parse object with circular references.
     */
    public function testParseObjectWithCircularReferences()
    {
        $start    = ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED;
        $complete = ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED;
        $expected = [
            //          level link name   type        id  attributes         meta
            [$start,    1,    '',         'people',   9,  $this->authorAttr, null],
            [$start,    2,    'comments', 'comments', 5,  $this->commAttr5,  null],
            [$start,    3,    'author',   'people',   9,  $this->authorAttr, null],
            [$complete, 2,    'comments', 'comments', 5,  $this->commAttr5,  null],
            [$start,    2,    'comments', 'comments', 12, $this->commAttr12, null],
            [$start,    3,    'author',   'people',   9,  $this->authorAttr, null],
            [$complete, 2,    'comments', 'comments', 12, $this->commAttr12, null],
            [$complete, 1,    '',         'people',   9,  $this->authorAttr, null],
        ];

        $allReplies = [];
        foreach ($this->parser->parse($this->author) as $reply) {
            /** @var ParserReplyInterface $reply */
            $allReplies[] = $this->replyToArray($reply);
        }
        $this->assertEquals($expected, $allReplies);
    }

    /**
     * Test parse link as reference.
     */
    public function testParseLinkReferences()
    {
        $this->post->{Post::LINK_AUTHOR} = null;

        $start      = ParserReplyInterface::REPLY_TYPE_RESOURCE_STARTED;
        $startRef   = ParserReplyInterface::REPLY_TYPE_REFERENCE_STARTED;
        $startNull  = ParserReplyInterface::REPLY_TYPE_NULL_RESOURCE_STARTED;
        $complete   = ParserReplyInterface::REPLY_TYPE_RESOURCE_COMPLETED;
        $expected   = [
            //             level link name   type        id    attributes         meta
            [$start,       1,    '',         'posts',    1,    $this->postAttr, null],
            [$startNull,   2,    'author',   '',         null, null,            null],
            [$startRef,    2,    'comments', '',         null, null,            null],
            [$complete,    1,    '',         'posts',    1,    $this->postAttr, null],
        ];

        $allReplies = [];
        foreach ($this->parser->parse($this->post) as $reply) {
            /** @var ParserReplyInterface $reply */
            $allReplies[] = $this->replyToArray($reply);
        }
        $this->assertEquals($expected, $allReplies);
    }

    /**
     * @param ParserReplyInterface $reply
     *
     * @return array
     */
    private function replyToArray(ParserReplyInterface $reply)
    {
        $current  = $reply->getStack()->end();
        $link     = $current->getLinkObject();
        $resource = $current->getResourceObject();
        return [
            $reply->getReplyType(),
            $current->getLevel(),
            $link     === null ? null : $link->getName(),
            $resource === null ? null : $resource->getType(),
            $resource === null ? null : $resource->getId(),
            $resource === null ? null : $resource->getAttributes(),
            $resource === null ? null : $resource->getMeta(),
        ];
    }
}
