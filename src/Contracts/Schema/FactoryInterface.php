<?php namespace Neomerx\JsonApi\Contracts\Schema;

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

/**
 * @package Neomerx\JsonApi
 */
interface FactoryInterface
{
    /**
     * @param string            $name
     * @param object|array|null $data
     * @param string|null       $selfSubUrl
     * @param string|null       $relatedSubUrl
     * @param bool              $isShowAsRef
     * @param bool              $isShowSelf
     * @param bool              $isShowRelated
     * @param bool              $isShowMeta
     * @param bool              $isIncluded
     * @param mixed             $selfControllerData
     * @param mixed             $relatedControllerData
     *
     * @return LinkObjectInterface
     */
    public function createLinkObject(
        $name,
        $data,
        $selfSubUrl,
        $relatedSubUrl,
        $isShowAsRef,
        $isShowSelf,
        $isShowRelated,
        $isShowMeta,
        $isIncluded,
        $selfControllerData,
        $relatedControllerData
    );
}
