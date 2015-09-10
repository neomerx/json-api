<?php

namespace Neomerx\JsonApi\Parameters;

use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Parameters\HeadersCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveHeadersChecker implements HeadersCheckerInterface
{
    /**
     * @var ExceptionThrowerInterface
     */
    private $exceptionThrower;

    /**
     * @var CodecMatcherInterface
     */
    private $codecMatcher;

    /**
     * @param ExceptionThrowerInterface $exceptionThrower
     * @param CodecMatcherInterface     $codecMatcher
     */
    public function __construct(
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher
    ) {
        $this->exceptionThrower    = $exceptionThrower;
        $this->codecMatcher        = $codecMatcher;
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        // Note: for these checks the order is specified by spec. See details inside.
        $this->checkAcceptHeader($parameters);
        $this->checkContentTypeHeader($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkAcceptHeader(ParametersInterface $parameters)
    {
        $this->codecMatcher->matchEncoder($parameters->getAcceptHeader());

        // From spec: Servers MUST respond with a 406 Not Acceptable status code
        // if a request's Accept header contains the JSON API media type and all
        // instances of that media type are modified with media type parameters.

        // We return 406 if no match found for encoder (media type with or wo parameters)
        // If no encoders were configured for media types with parameters we return 406 anyway
        if ($this->codecMatcher->getEncoderHeaderMatchedType() === null) {
            $this->exceptionThrower->throwNotAcceptable();
        }
    }

    /**
     * @param ParametersInterface $parameters
     *
     * @return void
     */
    protected function checkContentTypeHeader(ParametersInterface $parameters)
    {
        // Do not allow specify more than 1 media type for input data. Otherwise which one is correct?
        if (count($parameters->getContentTypeHeader()->getMediaTypes()) > 1) {
            $this->exceptionThrower->throwBadRequest();
        }

        $this->codecMatcher->findDecoder($parameters->getContentTypeHeader());

        // From spec: Servers MUST respond with a 415 Unsupported Media Type status code
        // if a request specifies the header Content-Type: application/vnd.api+json with
        // any media type parameters.

        // We return 415 if no match found for decoder (media type with or wo parameters)
        // If no decoders were configured for media types with parameters we return 415 anyway
        if ($this->codecMatcher->getDecoderHeaderMatchedType() === null) {
            $this->exceptionThrower->throwUnsupportedMediaType();
        }
    }
}
