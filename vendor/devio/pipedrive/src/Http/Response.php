<?php

namespace Devio\Pipedrive\Http;

class Response
{
    /**
     * The response code.
     *
     * @var integer
     */
    protected $statusCode;

    /**
     * The response data.
     *
     * @var mixed
     */
    protected $content;

    /**
     * The response headers.
     *
     * @var array
     */
    private $headers;

    /**
     * Response constructor.
     *
     * @param       $statusCode
     * @param       $content
     * @param array $headers
     */
    public function __construct($statusCode, $content, $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function isSuccess()
    {
        if (! $this->getContent()) {
            return false;
        }

        return $this->getContent()->success;
    }

    /**
     * Get the request data.
     *
     * @return mixed[]|\stdClass
     */
    public function getData()
    {
        if ($this->isSuccess() && isset($this->getContent()->data)) {
            return $this->getContent()->data;
        }

        return null;
    }

    /**
     * Get the additional data array if any.
     *
     * @return mixed[]|\stdClass
     */
    public function getAdditionalData()
    {
        if ($this->isSuccess() && isset($this->getContent()->additional_data)) {
            return $this->getContent()->additional_data;
        }

        return null;
    }

    /**
     * Get the status code.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the headers array.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
