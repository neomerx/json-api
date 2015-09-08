<?php

namespace Neomerx\JsonApi\Exceptions;

use Neomerx\JsonApi\Contracts\Document\ErrorInterface;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

/**
 * Class ThrowableError
 * @package Neomerx\JsonApi
 */
class ThrowableError extends \RuntimeException implements ErrorInterface
{

    /**
     * @var int|string|null
     */
    private $idx;

    /**
     * @var null|array<string,\Neomerx\JsonApi\Contracts\Schema\LinkInterface>
     */
    private $links;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $detail;

    /**
     * @var mixed|null
     */
    private $source;

    /**
     * @var array|null
     */
    private $meta;

    /**
     * @param int|string|null    $idx
     * @param LinkInterface|null $aboutLink
     * @param string|null        $status
     * @param string|null        $code
     * @param string|null        $title
     * @param string|null        $detail
     * @param array|null         $source
     * @param array|null         $meta
     * @param \Exception|null     $previous
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $idx = null,
        LinkInterface $aboutLink = null,
        $status = null,
        $code = null,
        $title = null,
        $detail = null,
        array $source = null,
        array $meta = null,
        \Exception $previous = null
    ) {
        parent::__construct($title, $code, $previous);

        $this->idx = $idx;
        $this->links = $aboutLink;
        $this->status = $status;
        $this->title = $title;
        $this->detail = $detail;
        $this->source = $source;
        $this->meta = $meta;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->idx;
    }

    /**
     * @inheritdoc
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @inheritdoc
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
