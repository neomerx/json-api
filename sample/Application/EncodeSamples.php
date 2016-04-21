<?php namespace Neomerx\Samples\JsonApi\Application;

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
use \Neomerx\JsonApi\Document\Link;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\Samples\JsonApi\Models\Post;
use \Neomerx\Samples\JsonApi\Models\Site;
use \Neomerx\Samples\JsonApi\Models\Author;
use \Neomerx\Samples\JsonApi\Models\Comment;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\Samples\JsonApi\Schemas\PostSchema;
use \Neomerx\Samples\JsonApi\Schemas\SiteSchema;
use \Neomerx\Samples\JsonApi\Schemas\AuthorSchema;
use \Neomerx\Samples\JsonApi\Schemas\CommentSchema;
use \Neomerx\JsonApi\Encoder\Parameters\EncodingParameters;

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
    public function getBasicExample()
    {
        $author = Author::instance('123', 'John', 'Dow');

        $encoder = Encoder::instance([
            Author::class => AuthorSchema::class,
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com/api/v1'));

        $result = $encoder->encodeData($author);

        return $result;
    }

    /**
     * Get how objects are put to 'included'.
     *
     * @return string
     */
    public function getIncludedObjectsExample()
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
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        $result = $encoder->encodeData($site);

        return $result;
    }

    /**
     * Get sparse and field set filters.
     *
     * @return string
     */
    public function getSparseAndFieldSetsExample()
    {
        $author   = Author::instance('123', 'John', 'Dow');
        $comments = [
            Comment::instance('456', 'Included objects work as easy as basic ones', $author),
            Comment::instance('789', 'Let\'s try!', $author),
        ];
        $post = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site = Site::instance('1', 'JSON API Samples', [$post]);

        $options = new EncodingParameters([
            // Paths to be included. Note 'posts.comments' will not be shown.
            'posts.author'
        ], [
            // Attributes and relationships that should be shown
            'sites'  => ['name', 'posts'],
            'posts'  => ['author'],
            'people' => ['first_name'],
        ]);

        SiteSchema::$isShowCustomLinks = false;
        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new EncoderOptions(JSON_PRETTY_PRINT));

        $result = $encoder->encodeData($site, $options);

        return $result;
    }

    /**
     * Get sparse and field set filters.
     *
     * @return string
     */
    public function getTopLevelMetaAndLinksExample()
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
            Link::FIRST => new Link('http://example.com/people?first', null, true),
            Link::LAST  => new Link('http://example.com/people?last', null, true),
            Link::PREV  => new Link('http://example.com/people?prev', null, true),
            Link::NEXT  => new Link('http://example.com/people?next', null, true),
        ];

        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        $result = $encoder->withLinks($links)->withMeta($meta)->encodeData($author);

        return $result;
    }

    /**
     * Get how schema could change dynamically.
     *
     * @return array
     */
    public function getDynamicSchemaExample()
    {
        $site = Site::instance('1', 'JSON API Samples', []);

        $encoder = Encoder::instance([
            Site::class => SiteSchema::class,
        ], new EncoderOptions(JSON_PRETTY_PRINT));

        SiteSchema::$isShowCustomLinks = false;
        $noLinksResult = $encoder->encodeData($site);

        SiteSchema::$isShowCustomLinks = true;
        $withLinksResult = $encoder->encodeData($site);

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
     * @return mixed
     */
    public function runPerformanceTestForSmallNestedResources($iterations)
    {
        $closure = function () use ($iterations) {
            $options = new EncodingParameters(
                ['posts.author'],
                ['sites' => ['name'], 'people' => ['first_name']]
            );
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
                    ->withLinks([Link::SELF => new Link('http://example.com/sites/1?' . $rand, null, true)])
                    ->withMeta(['some' => ['meta' => 'information' . $rand]])
                    ->encodeData($site, $options);
            }
        };

        $timeSpent = null;
        $this->getTime($closure, $timeSpent);

        return $timeSpent;
    }

    /**
     * Run performance test one time for big collection of resources.
     *
     * @param int $numberOfItems
     *
     * @return mixed
     */
    public function runPerformanceTestForBigCollection($numberOfItems)
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

            $options = new EncodingParameters(
                ['posts.author', 'posts.comments'],
                ['sites' => ['name'], 'people' => ['first_name']]
            );
            $encoder = Encoder::instance([
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
                Site::class    => SiteSchema::class
            ]);

            $encoder->encodeData($sites, $options);
        };

        $timeSpent = null;
        $this->getTime($closure, $timeSpent);

        return $timeSpent;
    }

    /**
     * @param Closure $closure
     * @param float   &$time
     *
     * @return mixed
     */
    private function getTime(Closure $closure, &$time)
    {
        $time_start = microtime(true);
        try {
            return $closure();
        } finally {
            $time_end = microtime(true);
            $time     = $time_end - $time_start;
        }
    }
}
