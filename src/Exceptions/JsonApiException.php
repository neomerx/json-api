<?php namespace Neomerx\JsonApi\Exceptions;

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

use \Exception;
use \RuntimeException;
use \Neomerx\JsonApi\Document\Error;
use \Neomerx\JsonApi\I18n\Translator as T;

/**
 * @package Neomerx\JsonApi
 */
class JsonApiException extends RuntimeException
{
    /** Default HTTP code */
    const HTTP_CODE_BAD_REQUEST = 400;

    /** Default HTTP code */
    const HTTP_CODE_FORBIDDEN = 403;

    /** Default HTTP code */
    const HTTP_CODE_NOT_ACCEPTABLE = 406;

    /** Default HTTP code */
    const HTTP_CODE_CONFLICT = 409;

    /** Default HTTP code */
    const HTTP_CODE_UNSUPPORTED_MEDIA_TYPE = 415;

    /** Default HTTP code */
    const DEFAULT_HTTP_CODE = self::HTTP_CODE_BAD_REQUEST;

    /**
     * @var ErrorCollection
     */
    private $errors;

    /**
     * @var int
     */
    private $httpCode;

    /**
     * Constructor.
     *
     * @param Error|Error[]|ErrorCollection $errors
     * @param int                           $httpCode
     * @param Exception|null                $previous
     */
    public function __construct($errors, $httpCode = self::DEFAULT_HTTP_CODE, Exception $previous = null)
    {
        parent::__construct(T::t('JSON API error'), 0, $previous);

        $this->errors = new ErrorCollection();

        if ($errors instanceof ErrorCollection) {
            $this->addErrors($errors);
        } elseif (is_array($errors) === true) {
            $this->addErrorsFromArray($errors);
        } else {
            // should be Error
            $this->addError($errors);
        }

        $this->httpCode = $httpCode;
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error)
    {
        $this->errors[] = $error;
    }

    /**
     * @param ErrorCollection $errors
     *
     * @return void
     */
    public function addErrors(ErrorCollection $errors)
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @param Error[] $errors
     *
     * @return void
     */
    public function addErrorsFromArray(array $errors)
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param JsonApiException $exception
     */
    public static function throwException(JsonApiException $exception)
    {
        throw $exception;
    }
}
