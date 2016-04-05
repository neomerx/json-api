<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue49;

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
use \Neomerx\Tests\JsonApi\Data\Site;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;
use \Neomerx\JsonApi\Encoder\EncodingParameters;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
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
     * @var Site
     */
    private $site;

    /**
     * @var EncoderOptions
     */
    private $encoderOptions;

    /**
     * Set up.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->author   = Author::instance(9, 'Dan', 'Gebhardt');
        $this->comments = [
            Comment::instance(5, 'First!', $this->author),
            Comment::instance(12, 'I like XML better', $this->author),
        ];
        $this->post = Post::instance(
            1,
            'JSON API paints my bikeshed!',
            'Outside every fat man there was an even fatter man trying to close in',
            $this->author,
            $this->comments
        );
        $this->site = Site::instance(2, 'site name', [$this->post]);
        $this->encoderOptions = new EncoderOptions(0, 'http://example.com');
    }

    /**
     * Test override SHOW_DATA=false with included path parameter.
     */
    public function testOverrideShowDataWithIncluded()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = CustomEncoder::instance([
            Author::class => function ($factory, $container) {
                $schema = new AuthorSchema($factory, $container);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::META, ['tip' => 'could be included']);
                $schema->linkAddTo(Author::LINK_COMMENTS, AuthorSchema::SHOW_DATA, false);
                return $schema;
            },
            Comment::class => CommentSchema::class,
        ], $this->encoderOptions)->encodeData($this->author, new EncodingParameters(
            // include only these relationships
            [Author::LINK_COMMENTS]
        ));

        $expected = <<<EOL
        {
            "data":{
                "type" : "people",
                "id"   : "9",
                "attributes" : {
                    "first_name" : "Dan",
                    "last_name"  : "Gebhardt"
                },
                "relationships" : {
                    "comments" : {
                        "data" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ],
                        "meta" : { "tip" : "could be included" }
                    }
                },
                "links":{
                    "self":"http://example.com/people/9"
                }
            },
            "included" : [{
                "type" : "comments",
                "id"   : "5",
                "attributes" : {
                    "body" : "First!"
                },
                "relationships":{
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    }
                },
                "links" : { "self":"http://example.com/comments/5" }
            }, {
                "type" : "comments",
                "id"   : "12",
                "attributes" : {
                    "body" : "I like XML better"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "9" }
                    }
                },
                "links" : {
                    "self" : "http://example.com/comments/12"
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
