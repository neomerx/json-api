<?php declare(strict_types=1);

namespace Neomerx\JsonApi\I18n;

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

/**
 * @package Neomerx\JsonApi
 */
class Messages
{
    /**
     * @var array
     */
    private static $translations = [];

    /**
     * Try to translate the message and format it with the given parameters.
     *
     * @param string $message
     * @param mixed  ...$parameters
     *
     * @return string
     */
    public static function compose(string $message, ...$parameters): string
    {
        $translation = static::getTranslation($message);
        $result      = empty($parameters) === false ? \vsprintf($translation, $parameters) : $translation;

        return $result;
    }

    /**
     * Translate message if configured or return the original untranslated message.
     *
     * @param string $message
     *
     * @return string
     */
    public static function getTranslation(string $message): string
    {
        return static::$translations[$message] ?? $message;
    }

    /**
     * Set translations for messages.
     *
     * @param array $translations
     *
     * @return void
     */
    public static function setTranslations(array $translations): void
    {
        static::$translations = $translations;
    }
}
