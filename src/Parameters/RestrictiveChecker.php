<?php

namespace Neomerx\JsonApi\Parameters;

use \Neomerx\JsonApi\Contracts\Codec\CodecMatcherInterface;
use Neomerx\JsonApi\Contracts\Parameters\CombinedCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use \Neomerx\JsonApi\Contracts\Integration\ExceptionThrowerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveChecker implements CombinedCheckerInterface
{

    /**
     * @var RestrictiveHeaderChecker
     */
    private $headerChecker;

    /**
     * @var RestrictiveParameterChecker
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
        $this->headerChecker = new RestrictiveHeaderChecker(
            $exceptionThrower,
            $codecMatcher
        );

        $this->parameterChecker = new RestrictiveParameterChecker(
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
     * @param ParametersInterface $parameters
     * @return void
     */
    public function check(ParametersInterface $parameters)
    {
        $this->checkHeaders($parameters);
        $this->checkParameters($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkParameters(ParametersInterface $parameters)
    {
        $this->parameterChecker->checkParameters($parameters);
    }

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        $this->headerChecker->checkHeaders($parameters);
    }
}
