<?php

namespace Neomerx\JsonApi\Contracts\Parameters;

/**
 * Interface CombinedCheckerInterface
 * @package Neomerx\JsonApi
 */
interface ParametersCheckerInterface extends HeadersCheckerInterface, QueryCheckerInterface
{

    /**
     * Check headers and the parameters at once.
     *
     * @param ParametersInterface $parameters
     * @return void
     */
    public function check(ParametersInterface $parameters);
}
