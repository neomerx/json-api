<?php namespace Neomerx\Tests\JsonApi\Http;

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

use \Mockery;
use \LogicException;
use \Neomerx\JsonApi\Http\Request;
use \Psr\Http\Message\UriInterface;
use \Psr\Http\Message\StreamInterface;
use \Neomerx\Tests\JsonApi\BaseTestCase;

/**
 * @package Neomerx\Tests\JsonApi
 */
class RequestTest extends BaseTestCase
{
    /**
     * Test methods not used in the lib.
     */
    public function testNotUsedMethods()
    {
        $request = new Request(function () {
            return null;
        }, function () {
            return null;
        });

        $methodsAndParams = [
            'getAttribute'        => ['name'],
            'getAttributes'       => [],
            'getBody'             => [],
            'getCookieParams'     => [],
            'getHeaderLine'       => ['name'],
            'getHeaders'          => [],
            'getMethod'           => [],
            'getParsedBody'       => [],
            'getProtocolVersion'  => [],
            'getRequestTarget'    => [],
            'getServerParams'     => [],
            'getUploadedFiles'    => [],
            'getUri'              => [],
            'hasHeader'           => ['name'],
            'withAddedHeader'     => ['name', 'value'],
            'withAttribute'       => ['name', 'value'],
            'withBody'            => [Mockery::mock(StreamInterface::class)],
            'withCookieParams'    => [[]],
            'withHeader'          => ['name', 'value'],
            'withMethod'          => ['method'],
            'withoutAttribute'    => ['name'],
            'withoutHeader'       => ['name'],
            'withParsedBody'      => ['data'],
            'withProtocolVersion' => [123],
            'withQueryParams'     => [[]],
            'withRequestTarget'   => ['some target'],
            'withUploadedFiles'   => [[]],
            'withUri'             => [Mockery::mock(UriInterface::class)],
        ];

        foreach ($methodsAndParams as $method => $parameters) {
            $gotException = false;
            try {
                $this->assertTrue(is_array($parameters));
                call_user_func_array([$request, $method], $parameters);
            } catch (LogicException $exception) {
                $gotException = true;
            }
            $this->assertTrue($gotException);
        }
    }
}
