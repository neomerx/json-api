<?php

namespace Neomerx\JsonApi\Contracts\Parameters;

/**
 * Interface HeaderCheckerInterface
 * @package Neomerx\JsonApi
 */
interface HeaderCheckerInterface
{

    /**
     * @param ParametersInterface $parameters
     * @return void
     */
    public function checkHeaders(ParametersInterface $parameters);
}
