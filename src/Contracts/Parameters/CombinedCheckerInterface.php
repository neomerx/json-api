<?php

namespace Neomerx\JsonApi\Contracts\Parameters;

/**
 * Interface CombinedCheckerInterface
 * @package Neomerx\JsonApi
 */
interface CombinedCheckerInterface extends HeaderCheckerInterface, ParameterCheckerInterface
{

    /**
     * Check headers and the parameters at once.
     *
     * @param ParametersInterface $parameters
     * @return void
     */
    public function check(ParametersInterface $parameters);
}
