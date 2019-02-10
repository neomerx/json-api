<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Exceptions;

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

use Exception;
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Schema\ErrorCollection;

/**
 * @package Neomerx\JsonApi
 */
class JsonApiException extends BaseJsonApiException
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
     * @param ErrorInterface|iterable $errors
     * @param int                     $httpCode
     * @param Exception|null          $previous
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function __construct($errors, int $httpCode = self::DEFAULT_HTTP_CODE, Exception $previous = null)
    {
        parent::__construct('JSON API error', 0, $previous);

        if ($errors instanceof ErrorCollection) {
            $this->errors = clone $errors;
        } elseif (\is_iterable($errors) === true) {
            $this->errors = new ErrorCollection();
            $this->addErrors($errors);
        } else {
            // should be ErrorInterface
            $this->errors = new ErrorCollection();
            $this->addError($errors);
        }

        $this->httpCode = $httpCode;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return void
     */
    public function addError(ErrorInterface $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @param iterable $errors
     *
     * @return void
     */
    public function addErrors(iterable $errors): void
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }

    /**
     * @return ErrorCollection
     */
    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }
}
