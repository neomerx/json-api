<?php namespace Neomerx\JsonApi\Contracts\Document;

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
interface DocumentInterface
{
    /**
     * Set 'self' URL to top-level 'links' section.
     *
     * @param string $url
     *
     * @return void
     */
    public function setSelfUrlToDocumentLinks($url);

    /**
     * Set 'first' URL to top-level 'links' section.
     *
     * @param string $url
     *
     * @return void
     */
    public function setFirstUrlToDocumentLinks($url);

    /**
     * Set 'last' URL to top-level 'links' section.
     *
     * @param string $url
     *
     * @return void
     */
    public function setLastUrlToDocumentLinks($url);

    /**
     * Set 'prev' URL to top-level 'links' section.
     *
     * @param string $url
     *
     * @return void
     */
    public function setPrevUrlToDocumentLinks($url);

    /**
     * Set 'next' URL to top-level 'links' section.
     *
     * @param string $url
     *
     * @return void
     */
    public function setNextUrlToDocumentLinks($url);

    /**
     * Set arbitrary meta-information about primary data to top-level 'meta' section.
     *
     * @param object|array $meta
     *
     * @return void
     */
    public function setMetaToDocument($meta);

    /**
     * Add element to 'included' top-level section.
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function addToIncluded(ElementInterface $element);

    /**
     * Add element to 'data' top-level section.
     *
     * @param ElementInterface $element
     *
     * @return void
     */
    public function addToData(ElementInterface $element);

    /**
     * Add information to 'errors' top-level section.
     *
     * If you add error information no other elements will be in output document.
     *
     * @param ErrorInterface $error
     *
     * @return void
     */
    public function addError(ErrorInterface $error);

    /**
     * Get document as array.
     *
     * @return array
     */
    public function getDocument();
}
