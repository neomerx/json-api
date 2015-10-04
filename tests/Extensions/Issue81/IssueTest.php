<?php namespace Neomerx\Tests\JsonApi\Extensions\Issue81;

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

use \Closure;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Tests\JsonApi\Data\Author;
use \Neomerx\Tests\JsonApi\BaseTestCase;
use \Neomerx\Tests\JsonApi\Data\Comment;
use \Neomerx\JsonApi\Contracts\Schema\ContainerInterface;
use \Neomerx\JsonApi\Contracts\Schema\SchemaFactoryInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class IssueTest extends BaseTestCase
{
    /**
     * Create schema for identity object.
     *
     * @param string  $classType
     * @param Closure $identityClosure
     *
     * @return Closure
     */
    private function createIdentitySchema($classType, Closure $identityClosure)
    {
        return function (
            SchemaFactoryInterface $factory,
            ContainerInterface $container
        ) use (
            $classType,
            $identityClosure
        ) {
            $schema = $factory->createIdentitySchema($container, $classType, $identityClosure);

            return $schema;
        };
    }

    /**
     * Test encoder will encode identities.
     *
     * @see https://github.com/neomerx/json-api/issues/81
     */
    public function testEnheritedEncoder()
    {
        $author  = Author::instance('321', 'John', 'Dow');
        $comment = Comment::instance('123', 'Comment body', $author);
        $author->{Author::LINK_COMMENTS} = [$comment];

        // AuthorSchema is will provide JSON-API type however anything else from it won't be used
        $encoder = Encoder::instance([
            Author::class         => AuthorSchema::class,
            Comment::class        => CommentSchema::class,
            AuthorIdentity::class => $this->createIdentitySchema(Author::class, function (AuthorIdentity $identity) {
                return $identity->idx;
            }),
        ]);

        $actual = $encoder->encodeData($comment);

        $expected = <<<EOL
        {
            "data" : {
                "type"       : "comments",
                "id"         : "123",
                "attributes" : {
                    "body" : "Comment body"
                },
                "relationships" : {
                    "author" : {
                        "data" : { "type" : "people", "id" : "321" }
                    }
                },
                "links" : {
                    "self" : "/comments/123"
                }
            }
        }
EOL;
        // remove formatting from 'expected'
        $expected = json_encode(json_decode($expected));

        $this->assertEquals($expected, $actual);
    }
}
