<?php

namespace Neomerx\JsonApi\Parameters;

use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Parameters\ParametersCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParametersChecker implements ParametersCheckerInterface
{

    /**
     * @var RestrictiveHeadersChecker
     */
    private $headerChecker;

    /**
     * @var RestrictiveQueryChecker
     */
    private $parameterChecker;

    /**
     * @param ExceptionThrowerInterface $exceptionThrower
     * @param CodecMatcherInterface     $codecMatcher
     * @param bool                      $allowUnrecognized
     * @param array|null                $includePaths
     * @param array|null                $fieldSetTypes
     * @param array|null                $sortParameters
     * @param array|null                $pagingParameters
     * @param array|null                $filteringParameters
     */
    public function __construct(
        ExceptionThrowerInterface $exceptionThrower,
        CodecMatcherInterface $codecMatcher,
        $allowUnrecognized = false,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->headerChecker = new RestrictiveHeadersChecker(
            $exceptionThrower,
            $codecMatcher
        );

        $this->parameterChecker = new RestrictiveQueryChecker(
            $exceptionThrower,
            $allowUnrecognized,
            $includePaths,
            $fieldSetTypes,
            $sortParameters,
            $pagingParameters,
            $filteringParameters
        );
    }

    /**
     * @inheritdoc
     */
    public function check(ParametersInterface $parameters)
    {
        $this->checkHeaders($parameters);
        $this->checkQuery($parameters);
    }

    /**
     * @inheritdoc
     */
    public function checkQuery(ParametersInterface $parameters)
    {
        $this->parameterChecker->checkQuery($parameters);
    }

    /**
     * @inheritdoc
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        $this->headerChecker->checkHeaders($parameters);
    }
}
