<?php namespace Neomerx\JsonApi\Http\Query;

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

use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;
use Neomerx\JsonApi\Document\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * @package Neomerx\JsonApi
 */
class BaseQueryParser implements BaseQueryParserInterface
{
    /** Message */
    public const MSG_ERR_INVALID_PARAMETER = 'Invalid Parameter.';

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string[]|null
     */
    private $messages;

    /**
     * @param array         $parameters
     * @param string[]|null $messages
     */
    public function __construct(array $parameters = [], array $messages = null)
    {
        $this->setParameters($parameters);
        $this->messages = $messages;
    }

    /**
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIncludes(): iterable
    {
        if (array_key_exists(static::PARAM_INCLUDE, $this->getParameters()) === true) {
            $splitByDot = function (string $path): iterable {
                foreach ($this->splitStringAndCheckNoEmpties(static::PARAM_INCLUDE, $path, '.') as $link) {
                    yield $link;
                }
            };

            $includes = $this->getParameters()[static::PARAM_INCLUDE];
            foreach ($this->splitCommaSeparatedStringAndCheckNoEmpties(static::PARAM_INCLUDE, $includes) as $path) {
                yield $path => $splitByDot($path);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getFields(): iterable
    {
        if (array_key_exists(static::PARAM_FIELDS, $this->getParameters()) === true) {
            $fields = $this->getParameters()[static::PARAM_FIELDS];
            if (is_array($fields) === false || empty($fields) === true) {
                throw new JsonApiException($this->createParameterError(static::PARAM_FIELDS));
            }

            foreach ($fields as $type => $fieldList) {
                yield $type => $this->splitCommaSeparatedStringAndCheckNoEmpties($type, $fieldList);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function getSorts(): iterable
    {
        if (array_key_exists(static::PARAM_SORT, $this->getParameters()) === true) {
            $sorts = $this->getParameters()[static::PARAM_SORT];
            foreach ($this->splitCommaSeparatedStringAndCheckNoEmpties(static::PARAM_SORT, $sorts) as $orderAndField) {
                switch ($orderAndField[0]) {
                    case '-':
                        $isAsc = false;
                        $field = substr($orderAndField, 1);
                        break;
                    case '+':
                        $isAsc = true;
                        $field = substr($orderAndField, 1);
                        break;
                    default:
                        $isAsc = true;
                        $field = $orderAndField;
                        break;
                }

                yield $field => $isAsc;
            }
        }
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     * @param string       $separator
     *
     * @return iterable
     */
    protected function splitString(string $paramName, $shouldBeString, string $separator): iterable
    {
        if (is_string($shouldBeString) === false || ($trimmed = trim($shouldBeString)) === '') {
            throw new JsonApiException($this->createParameterError($paramName));
        }

        foreach (explode($separator, $trimmed) as $value) {
            yield $value;
        }
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     * @param string       $separator
     *
     * @return iterable
     */
    protected function splitStringAndCheckNoEmpties(string $paramName, $shouldBeString, string $separator): iterable
    {
        foreach ($this->splitString($paramName, $shouldBeString, $separator) as $value) {
            $trimmedValue = trim($value);
            if (($trimmedValue) === '') {
                throw new JsonApiException($this->createParameterError($paramName));
            }

            yield $trimmedValue;
        }
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     *
     * @return iterable
     */
    protected function splitCommaSeparatedStringAndCheckNoEmpties(string $paramName, $shouldBeString): iterable
    {
        return $this->splitStringAndCheckNoEmpties($paramName, $shouldBeString, ',');
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param string $parameterName
     *
     * @return Error
     */
    protected function createParameterError(string $parameterName): Error
    {
        return $this->createQueryError($parameterName, static::MSG_ERR_INVALID_PARAMETER);
    }

    /**
     * @param string $name
     * @param string $title
     *
     * @return Error
     */
    protected function createQueryError(string $name, string $title): Error
    {
        $title  = $this->getMessage($title);
        $source = [Error::SOURCE_PARAMETER => $name];
        $error  = new Error(null, null, null, null, $title, null, $source);

        return $error;
    }

    /**
     * @param string $message
     *
     * @return string
     */
    protected function getMessage(string $message): string
    {
        $hasTranslation = $this->messages !== null && array_key_exists($message, $this->messages) === false;

        return $hasTranslation === true ? $this->messages[$message] : $message;
    }
}
