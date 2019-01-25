<?php declare(strict_types=1);

namespace Neomerx\Tests\JsonApi\Schema;

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

use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Schema\SchemaContainer;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Neomerx\Tests\JsonApi\Data\Models\Author;
use Neomerx\Tests\JsonApi\Data\Models\Comment;
use Neomerx\Tests\JsonApi\Data\Models\Post;
use Neomerx\Tests\JsonApi\Data\Schemas\AuthorSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\CommentSchema;
use Neomerx\Tests\JsonApi\Data\Schemas\PostSchema;

/**
 * @package Neomerx\Tests\JsonApi
 */
class SchemaContainerTest extends BaseTestCase
{
    /**
     * Test register and get schema.
     */
    public function testRegisterAndGet(): void
    {
        $factory       = $this->createFactory();
        $commentSchema = new CommentSchema($factory);
        $postSchema    = new PostSchema($factory);
        $container     = $factory->createSchemaContainer([
            Author::class  => AuthorSchema::class,
            Comment::class => $commentSchema,
            Post::class    => function () use ($postSchema): SchemaInterface {
                return $postSchema;
            },
        ]);

        $author  = $this->createAuthor();
        $comment = $this->createComment();
        $post    = $this->createPost();

        self::assertTrue($container->hasSchema($author));
        self::assertNotNull($container->getSchema($author));
        self::assertTrue($container->hasSchema($comment));
        self::assertSame($commentSchema, $container->getSchema($comment));
        self::assertTrue($container->hasSchema($post));
        self::assertSame($postSchema, $container->getSchema($post));
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\InvalidArgumentException
     */
    public function testInvalidModelClass(): void
    {
        $notExistingClass = self::class . 'xxx';

        $this->createFactory()->createSchemaContainer([$notExistingClass => AuthorSchema::class]);
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\InvalidArgumentException
     */
    public function testInvalidSchemaClass(): void
    {
        $notSchemaClass = self::class;

        $this->createFactory()->createSchemaContainer([Author::class => $notSchemaClass]);
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\InvalidArgumentException
     */
    public function testModelCannotHaveTwoSchemas(): void
    {
        $container = $this->createFactory()->createSchemaContainer([Author::class  => AuthorSchema::class]);

        assert($container instanceof SchemaContainer);

        $container->register(Author::class, CommentSchema::class);
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\LogicException
     */
    public function testDefaultSchemaDoNotProvideIdentifierMeta(): void
    {
        $schema = new CommentSchema($this->createFactory());

        $schema->getIdentifierMeta($this->createComment());
    }

    /**
     * @expectedException \Neomerx\JsonApi\Exceptions\LogicException
     */
    public function testDefaultSchemaDoNotProvideResourceMeta(): void
    {
        $schema = new CommentSchema($this->createFactory());

        $schema->getResourceMeta($this->createComment());
    }

    /**
     * @return Author
     */
    private function createAuthor(): Author
    {
        return Author::instance(1, 'FirstName', 'LastName');
    }

    /**
     * @return Comment
     */
    private function createComment(): Comment
    {
        return Comment::instance(321, 'Comment body');
    }

    /**
     * @return Post
     */
    private function createPost(): Post
    {
        return Post::instance(321, 'Post Title', 'Post body');
    }
}
