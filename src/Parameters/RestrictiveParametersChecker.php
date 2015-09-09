<?php

namespace Neomerx\JsonApi\Parameters;

use Neomerx\JsonApi\Contracts\Parameters\HeadersCheckerInterface;
use Neomerx\JsonApi\Contracts\Parameters\ParametersCheckerInterface;
use \Neomerx\JsonApi\Contracts\Parameters\ParametersInterface;
use Neomerx\JsonApi\Contracts\Parameters\QueryCheckerInterface;

/**
 * @package Neomerx\JsonApi
 */
class RestrictiveParametersChecker implements ParametersCheckerInterface
{

    /**
     * @var HeadersCheckerInterface
     */
    private $headerChecker;

    /**
     * @var QueryCheckerInterface
     */
    private $queryChecker;

    /**
     * @param HeadersCheckerInterface $headersChecker
     * @param QueryCheckerInterface $queryChecker
     */
    public function __construct(HeadersCheckerInterface $headersChecker, QueryCheckerInterface $queryChecker)
    {
        $this->headerChecker = $headersChecker;
        $this->queryChecker = $queryChecker;
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
        $this->queryChecker->checkQuery($parameters);
    }

    /**
     * @inheritdoc
     */
    public function checkHeaders(ParametersInterface $parameters)
    {
        $this->headerChecker->checkHeaders($parameters);
    }
}
