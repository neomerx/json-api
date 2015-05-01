<?php namespace Neomerx\Tests\JsonApi\Encoder;

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

use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Post;
use \Neomerx\Tests\JsonApi\Data\Site;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\Tests\JsonApi\Data\PostSchema;
use \Neomerx\Tests\JsonApi\Data\SiteSchema;
use \Neomerx\JsonApi\Encoder\EncodingOptions;
use \Neomerx\Tests\JsonApi\Data\AuthorSchema;
use \Neomerx\Tests\JsonApi\Data\CommentSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class EncodeSparseAndFieldSetsTest extends BaseTestCase
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
    }

    /**
     * Test encode nested included objects with cyclic dependencies and sparse support.
     */
    public function testEncodeWithRecursiveIncludedObjects()
    {
        $this->author->{Author::LINK_COMMENTS} = $this->comments;

        $actual = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => function ($factory, $container) {
                $schema = new PostSchema($factory, $container);
                $schema->linkAddTo(Post::LINK_AUTHOR, PostSchema::INCLUDED, true);
                $schema->linkAddTo(Post::LINK_COMMENTS, PostSchema::INCLUDED, true);
                return $schema;
            },
            Site::class    => SiteSchema::class,
        ])->encode($this->site, null, null, new EncodingOptions(
            [
                // include only this relations
                Site::LINK_POSTS,
                Site::LINK_POSTS . '.' . Post::LINK_COMMENTS,
            ],
            null // no filter for attributes
        ));

        $expected = <<<EOL
        {
            "data" : {
                "type"  : "sites",
                "id"    : "2",
                "name"  : "site name",
                "links" : {
                    "self" : "http://example.com/sites/2",
                    "posts" : {
                        "linkage" : {
                            "type" : "posts",
                            "id" : "1"
                        }
                    }
                }
            },
            "included" : [{
                "type"  : "comments",
                "id"    : "5",
                "body"  : "First!",
                "links" : {
                    "self"   : "http://example.com/comments/5",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    }
                }
            }, {
                "type"  : "comments",
                "id"    : "12",
                "body"  : "I like XML better",
                "links" : {
                    "self"   : "http://example.com/comments/12",
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    }
                }
            },{
                "type"  : "posts",
                "id"    : "1",
                "title" : "JSON API paints my bikeshed!",
                "body"  : "Outside every fat man there was an even fatter man trying to close in",
                "links" : {
                    "author" : {
                        "linkage" : { "type" : "people", "id" : "9" }
                    },
                    "comments" : {
                        "linkage" : [
                            { "type" : "comments", "id" : "5" },
                            { "type" : "comments", "id" : "12" }
                        ]
                    }
                }
            }]
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
