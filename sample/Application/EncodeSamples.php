<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi\Application;

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

use Closure;
use Neomerx\JsonApi\Encoder\Encoder;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\Samples\JsonApi\Models\Author;
use Neomerx\Samples\JsonApi\Models\Comment;
use Neomerx\Samples\JsonApi\Models\Post;
use Neomerx\Samples\JsonApi\Models\Site;
use Neomerx\Samples\JsonApi\Schemas\AuthorSchema;
use Neomerx\Samples\JsonApi\Schemas\CommentSchema;
use Neomerx\Samples\JsonApi\Schemas\PostSchema;
use Neomerx\Samples\JsonApi\Schemas\SiteSchema;

/**
 * @package Neomerx\Samples\JsonApi
 */
class EncodeSamples
{
    /**
     * Get basic usage.
     *
     * @return string
     */
    public function getBasicExample(): string
    {
        $author = Author::instance('123', 'John', 'Dow');

        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ])->withEncodeOptions(JSON_PRETTY_PRINT);

        $result = $encoder->withUrlPrefix('http://example.com/api/v1')->encodeData($author);

        return $result;
    }

    /**
     * Get how objects are put to 'included'.
     *
     * @return string
     */
    public function getIncludedObjectsExample(): string
    {
        $author   = Author::instance('123', 'John', 'Dow');
        $comments = [
            Comment::instance('456', 'Included objects work as easy as basic ones', $author),
            Comment::instance('789', 'Let\'s try!', $author),
        ];
        $post     = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site     = Site::instance('1', 'JSON API Samples', [$post]);

        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ])->withEncodeOptions(JSON_PRETTY_PRINT);

        $result = $encoder
            ->withUrlPrefix('http://example.com')
            ->withIncludedPaths([
                'posts',
                'posts.author',
                'posts.comments',
            ])
            ->encodeData($site);

        return $result;
    }

    /**
     * Get sparse and field set filters.
     *
     * @return string
     */
    public function getSparseAndFieldSetsExample(): string
    {
        $author   = Author::instance('123', 'John', 'Dow');
        $comments = [
            Comment::instance('456', 'Included objects work as easy as basic ones', $author),
            Comment::instance('789', 'Let\'s try!', $author),
        ];
        $post = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site = Site::instance('1', 'JSON API Samples', [$post]);

        SiteSchema::$isShowCustomLinks = false;
        $encoder                       = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ])->withEncodeOptions(JSON_PRETTY_PRINT);

        $result = $encoder
            ->withIncludedPaths([
                // Paths to be included. Note 'posts.comments' will not be shown.
                'posts',
                'posts.author',
            ])
            ->withFieldSets([
                // Attributes and relationships that should be shown
                'sites'  => ['name', 'posts'],
                'posts'  => ['author'],
                'people' => ['first_name'],
            ])
            ->encodeData($site);

        return $result;
    }

    /**
     * Get sparse and field set filters.
     *
     * @return string
     */
    public function getTopLevelMetaAndLinksExample(): string
    {
        $author = Author::instance('123', 'John', 'Dow');
        $meta   = [
            "copyright" => "Copyright 2015 Example Corp.",
            "authors"   => [
                "Yehuda Katz",
                "Steve Klabnik",
                "Dan Gebhardt"
            ]
        ];
        $links  = [
            Link::FIRST => new Link(false,'http://example.com/people?first', false),
            Link::LAST  => new Link(false,'http://example.com/people?last', false),
            Link::PREV  => new Link(false,'http://example.com/people?prev', false),
            Link::NEXT  => new Link(false,'http://example.com/people?next', false),
        ];

        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ])->withEncodeOptions(JSON_PRETTY_PRINT);

        $result = $encoder
            ->withLinks($links)
            ->withMeta($meta)
            ->withUrlPrefix('http://example.com')
            ->encodeData($author);

        return $result;
    }

    /**
     * Get how schema could change dynamically.
     *
     * @return array
     */
    public function getDynamicSchemaExample(): array
    {
        $site = Site::instance('1', 'JSON API Samples', []);

        $encoder = Encoder::instance([
            Site::class => SiteSchema::class,
        ])->withEncodeOptions(JSON_PRETTY_PRINT);

        SiteSchema::$isShowCustomLinks = false;
        $noLinksResult                 = $encoder->encodeData($site);

        SiteSchema::$isShowCustomLinks = true;
        $withLinksResult               = $encoder->encodeData($site);

        return [
            $noLinksResult,
            $withLinksResult,
        ];
    }

    /**
     * Run performance test for many times for small nested resources.
     *
     * @param int $iterations
     *
     * @return array
     */
    public function runPerformanceTestForSmallNestedResources(int $iterations): array
    {
        $closure = function () use ($iterations) {
            $encoder = Encoder::instance([
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
                Site::class    => SiteSchema::class
            ]);

            for ($index = 0; $index < $iterations; ++$index) {
                $rand = rand();

                $author   = Author::instance('123', 'John' . $rand, 'Dow' . $rand);
                $comments = [
                    Comment::instance('456', 'Included objects work as easy as basic ones' . $rand, $author),
                    Comment::instance('789', 'Let\'s try!' . $rand, $author),
                ];
                $post = Post::instance('321', 'Included objects' . $rand, 'Yes, it is supported', $author, $comments);
                $site = Site::instance('1', 'JSON API Samples' . $rand, [$post]);

                $encoder
                    ->withLinks([Link::SELF => new Link(false,'http://example.com/sites/1?' . $rand, false)])
                    ->withMeta(['some' => ['meta' => 'information' . $rand]])
                    ->withIncludedPaths(['posts.author'])
                    ->withFieldSets(['sites' => ['name'], 'people' => ['first_name']])
                    ->encodeData($site);
            }
        };

        $timeSpent = 0.0;
        $bytesUsed = 0;
        $this->getTime($closure, $timeSpent, $bytesUsed);

        return [$timeSpent, $bytesUsed];
    }

    /**
     * Run performance test one time for big collection of resources.
     *
     * @param int $numberOfItems
     *
     * @return array
     */
    public function runPerformanceTestForBigCollection(int $numberOfItems): array
    {
        $closure = function() use ($numberOfItems) {
            $sites = [];
            for ($index = 0; $index < $numberOfItems; ++$index) {
                $rand = rand();

                $author   = Author::instance('123', 'John' . $rand, 'Dow' . $rand);
                $comments = [
                    Comment::instance('456', 'Included objects work as easy as basic ones' . $rand, $author),
                    Comment::instance('789', 'Let\'s try!' . $rand, $author),
                ];
                $post = Post::instance('321', 'Included objects' . $rand, 'Yes, it is supported', $author, $comments);
                $site = Site::instance('1', 'JSON API Samples' . $rand, [$post]);

                $sites[] = $site;
            }

            $encoder = Encoder::instance([
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
                Site::class    => SiteSchema::class
            ]);

            $encoder
                ->withIncludedPaths(['posts.author', 'posts.comments'])
                ->withFieldSets(['sites' => ['name'], 'people' => ['first_name']])
                ->encodeData($sites);
        };

        $timeSpent = 0.0;
        $bytesUsed = 0;
        $this->getTime($closure, $timeSpent, $bytesUsed);

        return [$timeSpent, $bytesUsed];
    }

    /**
     * @param Closure $closure
     * @param float   &$time
     * @param int     &$memory
     *
     * @return mixed
     */
    private function getTime(Closure $closure, float &$time, int &$memory)
    {
        $timeStart  = microtime(true);
        $bytesStart = memory_get_usage();
        try {
            return $closure();
        } finally {
            $bytesEnd = memory_get_usage();
            $timeEnd  = microtime(true);

            $time   = $timeEnd - $timeStart;
            $memory = $bytesEnd - $bytesStart;
        }
    }
}
