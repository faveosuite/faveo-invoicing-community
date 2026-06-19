<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common\Twitter;

/**
 * The result of the most recent API request.
 *
 * @author Abraham Williams <abraham@abrah.am>
 */
class Response
{
    /** @var string|null API path from the most recent request */
    private $apiPath;

    /** @var int HTTP status code from the most recent request */
    private $httpCode = 0;

    /** @var array<mixed> HTTP headers from the most recent request */
    /**
     * @var array<mixed>
     */
    private $headers = [];

    /** @var array<mixed>|object Response body from the most recent request */
    private array|object $body = [];

    /** @var array<mixed> HTTP headers from the most recent request that start with X */
    /**
     * @var array<mixed>
     */
    private $xHeaders = [];

    /**
     * @param  string  $apiPath
     */
    public function setApiPath($apiPath): void
    {
        $this->apiPath = $apiPath;
    }

    /**
     * @return string|null
     */
    public function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * @param  array<mixed>|object  $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @return array<mixed>|object
     */
    public function getBody(): array|object
    {
        return $this->body;
    }

    /**
     * @param  int  $httpCode
     */
    public function setHttpCode($httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param  array<mixed> $headers
     */
    public function setHeaders($headers): void
    {
        foreach ($headers as $key => $value) {
            if (str_starts_with((string) $key, 'x')) {
                $this->xHeaders[$key] = $value;
            }
        }

        $this->headers = $headers;
    }

    /**
     * @return array<mixed>     */
    public function getsHeaders()
    {
        return $this->headers;
    }

    /**
     * @param  array<mixed> $xHeaders
     */
    public function setXHeaders($xHeaders): void
    {
        $this->xHeaders = $xHeaders;
    }

    /**
     * @return array<mixed>     */
    public function getXHeaders()
    {
        return $this->xHeaders;
    }
}
