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

use \Neomerx\JsonApi\Encoder\Encoder;
use \Neomerx\JsonApi\Document\DocumentLinks;
use \Neomerx\JsonApi\Encoder\EncodingOptions;
use \Neomerx\JsonApi\Encoder\JsonEncodeOptions;

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
        ], new JsonEncodeOptions(JSON_PRETTY_PRINT));

        echo $encoder->encode($author) . PHP_EOL;
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
        $post     = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site     = Site::instance('1', 'JSON API Samples', [$post]);

        $encoder  = Encoder::instance([
            Author::class  => AuthorSchema::class,
            Comment::class => CommentSchema::class,
            Post::class    => PostSchema::class,
            Site::class    => SiteSchema::class
        ], new JsonEncodeOptions(JSON_PRETTY_PRINT));

        echo $encoder->encode($site) . PHP_EOL;
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
        $post     = Post::instance('321', 'Included objects', 'Yes, it is supported', $author, $comments);
        $site     = Site::instance('1', 'JSON API Samples', [$post]);

        $options  = new EncodingOptions(
            ['posts.author'], // Paths to be included. Note neither 'posts' nor 'posts.comments' will be shown.
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
        ], new JsonEncodeOptions(JSON_PRETTY_PRINT));

        echo $encoder->encode($site, null, null, $options) . PHP_EOL;
    }

    /**
     * Run performance test.
     *
     * @param int $iterations
     */
    private function runPerformanceTest($iterations)
    {
        for ($index = 0; $index < $iterations; ++$index) {
            $rand = rand();

            $author   = Author::instance('123', 'John' . $rand, 'Dow' . $rand);
            $comments = [
                Comment::instance('456', 'Included objects work as easy as basic ones' . $rand, $author),
                Comment::instance('789', 'Let\'s try!' . $rand, $author),
            ];
            $post     = Post::instance('321', 'Included objects' . $rand, 'Yes, it is supported', $author, $comments);
            $site     = Site::instance('1', 'JSON API Samples' . $rand, [$post]);

            $options = new EncodingOptions(
                ['posts.author'],
                ['sites' => ['name'], 'people' => ['first_name']]
            );
            $encoder = Encoder::instance([
                Author::class  => AuthorSchema::class,
                Comment::class => CommentSchema::class,
                Post::class    => PostSchema::class,
                Site::class    => SiteSchema::class
            ], new JsonEncodeOptions(JSON_PRETTY_PRINT));

            $encoder->encode(
                $site,
                new DocumentLinks('http://example.com/sites/1?' . $rand),
                ['some' => ['meta' => 'information' . $rand]],
                $options
            );
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
        } else {
            $num = $args['t'];
            $inv = empty($num) === true || is_numeric($num) === false || ctype_digit($num) == false || (int)$num <= 0;
            $num = $inv === true ? 1000 : (int)$num;

            echo "Neomerx JSON API performance test ($num iterations)..." . PHP_EOL;
            $this->runPerformanceTest($num);
        }
    }
}

(new Application())->main();