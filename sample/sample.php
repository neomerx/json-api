<?php

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

use \Neomerx\JsonApi\Schema\Link;
use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Encoder\EncoderOptions;
use \Neomerx\JsonApi\Parameters\EncodingParameters;

require './vendor/autoload.php';

/**
 * @package Neomerx\Samples\JsonApi
 */
class Application
{
    /**
     * Shows basic usage.
     */
    private function showBasicExample()
    {
        echo 'Neomerx JSON API sample application (basic usage)' . PHP_EOL;

        $author  = Author::instance('123', 'John', 'Dow');

        $encoder = Encoder::instance([
            Author::class  => AuthorSchema::class,
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com/api/v1'));

        echo $encoder->encodeData($author) . PHP_EOL;
    }

    /**
     * Shows how objects are put to 'included'.
     */
    private function showIncludedObjectsExample()
    {
        echo 'Neomerx JSON API sample application (included objects)' . PHP_EOL;

        $author   = Author::instance('123', 'John', 'Dow');
        $comments = [
            Comment::instance('456', 'Included objects work as easy as basic ones', $author),
            Comment::instance('789', 'Let\'s try!', $author),
        ];
        $post = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site = Site::instance('1', 'JSON API Samples', [$post]);

        $encoder  = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        echo $encoder->encodeData($site) . PHP_EOL;
    }

    /**
     * Shows sparse and field set filters.
     */
    private function showSparseAndFieldSetsExample()
    {
        echo 'Neomerx JSON API sample application (sparse and field sets)' . PHP_EOL;

        $author   = Author::instance('123', 'John', 'Dow');
        $comments = [
            Comment::instance('456', 'Included objects work as easy as basic ones', $author),
            Comment::instance('789', 'Let\'s try!', $author),
        ];
        $post = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site = Site::instance('1', 'JSON API Samples', [$post]);

        $options  = new EncodingParameters(
            ['posts.author'], // Paths to be included. Note 'posts.comments' will not be shown.
            [
                // Attributes and links that should be shown
                'sites'  => ['name'],
                'people' => ['first_name'],
            ]
        );
        $encoder  = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new EncoderOptions(JSON_PRETTY_PRINT));

        echo $encoder->encodeData($site, $options) . PHP_EOL;
    }

    /**
     * Shows sparse and field set filters.
     */
    private function showTopLevelMetaAndLinksExample()
    {
        echo 'Neomerx JSON API sample application (top level links and meta information)' . PHP_EOL;

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

        $encoder  = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new EncoderOptions(JSON_PRETTY_PRINT, 'http://example.com'));

        echo $encoder->withLinks($links)->withMeta($meta)->encodeData($author) . PHP_EOL;
    }

    /**
     * Shows how schema could change dynamically.
     */
    private function dynamicSchemaExample()
    {
        echo 'Neomerx JSON API sample application (dynamic schema)' . PHP_EOL;

        $site = Site::instance('1', 'JSON API Samples', []);

        $encoder = Encoder::instance([
            Site::class => SiteSchema::class,
        ], new EncoderOptions(JSON_PRETTY_PRINT));

        SiteSchema::$isShowCustomLinks = false;
        echo $encoder->encodeData($site) . PHP_EOL;

        SiteSchema::$isShowCustomLinks = true;
        echo $encoder->encodeData($site) . PHP_EOL;
    }

    /**
     * Run performance test for many times for small nested resources.
     *
     * @param int $iterations
     */
    private function runPerformanceTestForSmallNestedResources($iterations)
    {
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
            $post     = Post::instance('321', 'Included objects' . $rand, 'Yes, it is supported', $author, $comments);
            $site     = Site::instance('1', 'JSON API Samples' . $rand, [$post]);

            $encoder
                ->withLinks([Link::SELF => new Link('http://example.com/sites/1?' . $rand, null, true)])
                ->withMeta(['some' => ['meta' => 'information' . $rand]])
                ->encodeData($site, $options);
        }
    }

    /**
     * Run performance test one time for big collection of resources.
     *
     * @param int $numberOfItems
     */
    private function runPerformanceTestForBigCollection($numberOfItems)
    {
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
    }

    /**
     * @param Closure $closure
     * @param float  &$time
     *
     * @return mixed
     */
    private function getTime(\Closure $closure, &$time)
    {
        $time_start = microtime(true);
        try {
            return $closure();
        } finally {
            $time_end   = microtime(true);
            $time = $time_end - $time_start;
        }
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        $args = getopt('t::');

        if (isset($args['t']) === false) {
            $this->showBasicExample();
            $this->showIncludedObjectsExample();
            $this->showSparseAndFieldSetsExample();
            $this->showTopLevelMetaAndLinksExample();
            $this->dynamicSchemaExample();
        } else {
            $num = $args['t'];
            $inv = empty($num) === true || is_numeric($num) === false || ctype_digit($num) == false || (int)$num <= 0;
            $num = $inv === true ? 1000 : (int)$num;

            $time = 0;

            echo "Neomerx JSON API performance test ($num iterations for small resources)... ";
            $this->getTime(function () use ($num) {
                $this->runPerformanceTestForSmallNestedResources($num);
            }, $time);
            echo $time . ' seconds'  . PHP_EOL;

            echo "Neomerx JSON API performance test (1 iteration for $num resources)... ";
            $this->getTime(function () use ($num) {
                $this->runPerformanceTestForBigCollection($num);
            }, $time);
            echo $time . ' seconds'  . PHP_EOL;
        }
    }
}

(new Application())->main();
