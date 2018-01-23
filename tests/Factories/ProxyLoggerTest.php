<?php namespace Neomerx\Tests\JsonApi\Factories;

/**
 * Copyright 2015-2018 info@neomerx.com
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

use Mockery;
use Mockery\Mock;
use Neomerx\JsonApi\Factories\ProxyLogger;
use Neomerx\Tests\JsonApi\BaseTestCase;
use Psr\Log\LoggerInterface;

/**
 * @package Neomerx\Tests\JsonApi
 */
class ProxyLoggerTest extends BaseTestCase
{
    /**
     * Test set proxy logger.
     */
    public function testSetLogger()
    {
        $logger = new ProxyLogger();

        /** @var Mock $logMock */
        $this->assertNotNull($logMock = Mockery::mock(LoggerInterface::class));

        $logger->debug('Nothing hapens. Should not fail.');

        $logMock->shouldReceive('log')->once()->withAnyArgs()->andReturnUndefined();

        /** @var LoggerInterface $logMock */

        $logger->setLogger($logMock);

        $logger->debug('This one should trigger mock to log.');
    }
}
