<?php

namespace Neomerx\JsonApi\Exceptions\Renderer;

use Neomerx\JsonApi\Contracts\Exceptions\Renderer\ExceptionRendererInterface;
use Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;

/**
 * Class HttpCodeRenderer
 * @package Neomerx\JsonApi
 */
class HttpCodeRenderer implements ExceptionRendererInterface
{

    use ExceptionRendererTrait;

    /**
     * @var ResponsesInterface
     */
    protected $responses;

    /**
     * @param ResponsesInterface $responses
     */
    public function __construct(ResponsesInterface $responses)
    {
        $this->responses = $responses;
    }

    /**
     * @param \Exception $e
     * @return mixed
     */
    public function render(\Exception $e)
    {
        return $this->responses->getResponse(
            $this->getStatusCode(),
            $this->getMediaType(),
            null,
            $this->getSupportedExtensions(),
            $this->getHeaders()
        );
    }

}
