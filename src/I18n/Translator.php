<?php namespace Neomerx\JsonApi\I18n;

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
class Translator
{
    /**
     * @param string $format
     * @param array  $parameters
     *
     * @return string
     */
    public static function translate($format, array $parameters = [])
    {
        $result = empty($parameters) === false ? vsprintf($format, $parameters) : $format;

        return $result;
    }

    /**
     * @param string $format
     * @param array  $parameters
     *
     * @return string
     */
    public static function t($format, array $parameters = [])
    {
        return static::translate($format, $parameters);
    }
}
