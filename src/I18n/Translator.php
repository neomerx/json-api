<?php namespace Neomerx\JsonApi\I18n;

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

use \Neomerx\JsonApi\Contracts\I18n\TranslatorInterface;

/**
 * @package Neomerx\JsonApi
 */
class Translator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private static $translator = null;

    /**
     * @return TranslatorInterface
     */
    public static function getTranslator()
    {
        if (self::$translator === null) {
            self::$translator = new static;
        }

        return self::$translator;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public static function setTranslator(TranslatorInterface $translator)
    {
        self::$translator = $translator;
    }

    /**
     * @param string $format
     * @param array  $parameters
     *
     * @return string
     */
    public function translate($format, array $parameters = [])
    {
        $result = empty($parameters) === false ? vsprintf($format, $parameters) : $format;

        return $result;
    }

    /**
     * @param string $format
     * @param array  $parameters
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function t($format, array $parameters = [])
    {
        return static::getTranslator()->translate($format, $parameters);
    }
}
