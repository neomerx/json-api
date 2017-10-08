<?php namespace Neomerx\JsonApi\Contracts\Http\Query;

/**
 * Copyright 2015-2017 info@neomerx.com
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

use \Psr\Http\Message\ServerRequestInterface;
use \Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;

/**
 * @package Neomerx\JsonApi
 */
interface QueryParametersParserInterface
{
    /** Parameter name */
    const PARAM_INCLUDE = 'include';

    /** Parameter name */
    const PARAM_FIELDS = 'fields';

    /** Parameter name */
    const PARAM_PAGE = 'page';

    /** Parameter name */
    const PARAM_FILTER = 'filter';

    /** Parameter name */
    const PARAM_SORT = 'sort';

    /** @deprecated Use `parseQueryParameters($request->getQueryParams())` instead.
     * Parse input parameters from request.
     *
     * @param ServerRequestInterface $request
     *
     * @return EncodingParametersInterface
     */
    public function parse(ServerRequestInterface $request);

    /**
     * Parse input parameters from request.
     *
     * @param array $parameters
     *
     * @return EncodingParametersInterface
     */
    public function parseQueryParameters(array $parameters);
}
