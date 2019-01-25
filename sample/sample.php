<?php declare(strict_types=1); namespace Neomerx\Samples\JsonApi;

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

use Neomerx\Samples\JsonApi\Application\EncodeSamples;

require './vendor/autoload.php';

/** @noinspection PhpIllegalPsrClassPathInspection
 * @package Neomerx\Samples\JsonApi
 */
class Application
{
    /**
     * @var EncodeSamples
     */
    private $samples;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->samples = new EncodeSamples();
    }

    /**
     * Shows basic usage.
     *
     * @return void
     */
    private function showBasicExample(): void
    {
        echo 'Neomerx JSON API sample application (basic usage)' . PHP_EOL;
        echo $this->samples->getBasicExample() . PHP_EOL;
    }

    /**
     * Shows how objects are put to 'included'.
     *
     * @return void
     */
    private function showIncludedObjectsExample(): void
    {
        echo 'Neomerx JSON API sample application (included objects)' . PHP_EOL;
        echo $this->samples->getIncludedObjectsExample() . PHP_EOL;
    }

    /**
     * Shows sparse and field set filters.
     *
     * @return void
     */
    private function showSparseAndFieldSetsExample(): void
    {
        echo 'Neomerx JSON API sample application (sparse and field sets)' . PHP_EOL;
        echo $this->samples->getSparseAndFieldSetsExample() . PHP_EOL;
    }

    /**
     * Shows sparse and field set filters.
     *
     * @return void
     */
    private function showTopLevelMetaAndLinksExample(): void
    {
        echo 'Neomerx JSON API sample application (top level links and meta information)' . PHP_EOL;
        echo $this->samples->getTopLevelMetaAndLinksExample() . PHP_EOL;
    }

    /**
     * Shows how schema could change dynamically.
     *
     * @return void
     */
    private function dynamicSchemaExample(): void
    {
        echo 'Neomerx JSON API sample application (dynamic schema)' . PHP_EOL;

        $results = $this->samples->getDynamicSchemaExample();
        echo $results[0] . PHP_EOL;
        echo $results[1] . PHP_EOL;
    }

    /**
     * Run performance test for encoding many times a relatively small but nested resources.
     *
     * @param int $num
     *
     * @return void
     */
    private function runPerformanceTestForSmallNestedResources(int $num): void
    {
        echo "Neomerx JSON API performance test ($num iterations for small resources)... ";
        [$time, $bytes] = $this->samples->runPerformanceTestForSmallNestedResources($num);
        $bytes = number_format($bytes);
        echo "$time  seconds ($bytes bytes used)." . PHP_EOL;
    }

    /**
     * Run performance test for encoding once a big and nested resource.
     *
     * @param int $num
     *
     * @return void
     */
    private function runPerformanceTestForBigCollection(int $num): void
    {
        echo "Neomerx JSON API performance test (1 iteration for $num resources)... ";
        [$time, $bytes] = $this->samples->runPerformanceTestForBigCollection($num);
        $bytes = number_format($bytes);
        echo "$time  seconds ($bytes bytes used)." . PHP_EOL;
    }

    /**
     * Main entry point.
     *
     * @return void
     */
    public function main(): void
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

            $this->runPerformanceTestForSmallNestedResources($num);
            $this->runPerformanceTestForBigCollection($num);
        }
    }
}

(new Application())->main();
