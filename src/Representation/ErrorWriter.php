<?php declare(strict_types=1);

namespace Neomerx\JsonApi\Representation;

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

use Neomerx\JsonApi\Contracts\Representation\ErrorWriterInterface;
use Neomerx\JsonApi\Contracts\Schema\DocumentInterface;
use Neomerx\JsonApi\Contracts\Schema\ErrorInterface;

/**
 * @package Neomerx\JsonApi
 */
class ErrorWriter extends BaseWriter implements ErrorWriterInterface
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->data[DocumentInterface::KEYWORD_ERRORS] = [];
    }

    /**
     * @inheritdoc
     */
    public function addError(ErrorInterface $error): ErrorWriterInterface
    {
        $representation = \array_filter([
            DocumentInterface::KEYWORD_ERRORS_ID     => $error->getId(),
            DocumentInterface::KEYWORD_LINKS         => $this->getErrorLinksRepresentation($error),
            DocumentInterface::KEYWORD_ERRORS_STATUS => $error->getStatus(),
            DocumentInterface::KEYWORD_ERRORS_CODE   => $error->getCode(),
            DocumentInterface::KEYWORD_ERRORS_TITLE  => $error->getTitle(),
            DocumentInterface::KEYWORD_ERRORS_DETAIL => $error->getDetail(),
            DocumentInterface::KEYWORD_ERRORS_SOURCE => $error->getSource(),
         ]);

        if ($error->hasMeta() === true) {
            $representation[DocumentInterface::KEYWORD_ERRORS_META] = $error->getMeta();
        }

        // There is a special case when error representation is an empty array
        // Due to further json transform it must be an object otherwise it will be an empty array in json
        $representation = empty($representation) === false ? $representation : (object)$representation;

        $this->data[DocumentInterface::KEYWORD_ERRORS][] = $representation;

        return $this;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return array|null
     */
    private function getErrorLinksRepresentation(ErrorInterface $error): ?array
    {
        $linksRepresentation = null;

        if (($value = $error->getLinks()) !== null) {
            $linksRepresentation = $this->getLinksRepresentation($this->getUrlPrefix(), $value);
        }

        if (($value = $error->getTypeLinks()) !== null) {
            $linksRepresentation[DocumentInterface::KEYWORD_ERRORS_TYPE] =
                $this->getLinksListRepresentation($this->getUrlPrefix(), $value);
        }

        return $linksRepresentation;
    }
}
