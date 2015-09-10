<?php

namespace Neomerx\JsonApi\Exceptions\Renderer;

use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Exceptions\Renderer\ExceptionRendererInterface;
use Neomerx\JsonApi\Contracts\Responses\ResponsesInterface;

/**
 * Class ErrorsRenderer
 * @package Neomerx\JsonApi
 */
class ErrorsRenderer implements ExceptionRendererInterface
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
        /** Handle this safely so that a new Exception is not trigger while rendering a previous exception. */
        $content = ($e instanceof ErrorInterface) ? $this->getEncoder()->encodeErrors([$e]) : null;

        return $this->responses->getResponse(
            $this->getStatusCode(),
            $this->getMediaType(),
            $content,
            $this->getSupportedExtensions(),
            $this->getHeaders()
        );
    }
}
