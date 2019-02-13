<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Http\Query;

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

use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface as P;
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;

/**
 * @package Neomerx\JsonApi
 */
trait BaseQueryParserTrait
{
    /**
     * @param array  $parameters
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function getIncludes(array $parameters, string $errorTitle): iterable
    {
        if (\array_key_exists(P::PARAM_INCLUDE, $parameters) === true) {
            $paramName = P::PARAM_INCLUDE;
            $includes  = $parameters[$paramName];
            foreach ($this->splitCommaSeparatedStringAndCheckNoEmpties($paramName, $includes, $errorTitle) as $path) {
                yield $path => $this->splitStringAndCheckNoEmpties($paramName, $path, '.', $errorTitle);
            }
        }
    }

    /**
     * @param array  $parameters
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function getFields(array $parameters, string $errorTitle): iterable
    {
        if (\array_key_exists(P::PARAM_FIELDS, $parameters) === true) {
            $fields = $parameters[P::PARAM_FIELDS];
            if (\is_array($fields) === false || empty($fields) === true) {
                throw new JsonApiException($this->createParameterError(P::PARAM_FIELDS, $errorTitle));
            }

            foreach ($fields as $type => $fieldList) {
                yield $type => $this->splitCommaSeparatedStringAndCheckNoEmpties($type, $fieldList, $errorTitle);
            }
        }
    }

    /**
     * @param array  $parameters
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function getSorts(array $parameters, string $errorTitle): iterable
    {
        if (\array_key_exists(P::PARAM_SORT, $parameters) === true) {
            $sorts  = $parameters[P::PARAM_SORT];
            $values = $this->splitCommaSeparatedStringAndCheckNoEmpties(P::PARAM_SORT, $sorts, $errorTitle);
            foreach ($values as $orderAndField) {
                switch ($orderAndField[0]) {
                    case '-':
                        $isAsc = false;
                        $field = \substr($orderAndField, 1);
                        break;
                    case '+':
                        $isAsc = true;
                        $field = \substr($orderAndField, 1);
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
     * @param array  $parameters
     * @param string $errorTitle
     *
     * @return iterable
     */
    protected function getProfileUrls(array $parameters, string $errorTitle): iterable
    {
        if (\array_key_exists(P::PARAM_PROFILE, $parameters) === true) {
            $encodedUrls = $parameters[P::PARAM_PROFILE];
            $decodedUrls = \urldecode($encodedUrls);
            yield from $this->splitSpaceSeparatedStringAndCheckNoEmpties(
                P::PARAM_PROFILE,
                $decodedUrls,
                $errorTitle
            );
        }
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     * @param string       $errorTitle
     *
     * @return iterable
     */
    private function splitCommaSeparatedStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $errorTitle
    ): iterable {
        return $this->splitStringAndCheckNoEmpties($paramName, $shouldBeString, ',', $errorTitle);
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     * @param string       $errorTitle
     *
     * @return iterable
     */
    private function splitSpaceSeparatedStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $errorTitle
    ): iterable {
        return $this->splitStringAndCheckNoEmpties($paramName, $shouldBeString, ' ', $errorTitle);
    }

    /**
     * @param string       $paramName
     * @param string|mixed $shouldBeString
     * @param string       $separator
     * @param string       $errorTitle
     *
     * @return iterable
     */
    private function splitStringAndCheckNoEmpties(
        string $paramName,
        $shouldBeString,
        string $separator,
        string $errorTitle
    ): iterable {
        if (\is_string($shouldBeString) === false || ($trimmed = \trim($shouldBeString)) === '') {
            throw new JsonApiException($this->createParameterError($paramName, $errorTitle));
        }

        foreach (\explode($separator, $trimmed) as $value) {
            $trimmedValue = \trim($value);
            if ($trimmedValue === '') {
                throw new JsonApiException($this->createParameterError($paramName, $errorTitle));
            }

            yield $trimmedValue;
        }
    }

    /**
     * @param string $paramName
     * @param string $errorTitle
     *
     * @return ErrorInterface
     */
    private function createParameterError(string $paramName, string $errorTitle): ErrorInterface
    {
        $source = [Error::SOURCE_PARAMETER => $paramName];
        $error  = new Error(null, null, null, null, null, $errorTitle, null, $source);

        return $error;
    }
}
