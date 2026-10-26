<?php

declare(strict_types=1);

namespace App\Http\Controllers\Common\Twitter;

/**
 * Handle setting and storing config for TwitterOAuth.
 *
 * @author Abraham Williams <abraham@abrah.am>
 *
 * @codeCoverageIgnore
 */
class Config
{
    /** @var int How long to wait for a response from the API */
    protected $timeout = 5;

    /** @var int how long to wait while connecting to the API */
    protected $connectionTimeout = 5;

    /**
     * Decode JSON Response as associative Array.
     *
     * @see http://php.net/manual/en/function.json-decode.php
     *
     * @var bool
     */
    protected $decodeJsonAsArray = false;

    /** @var string User-Agent header */
    protected $userAgent = 'TwitterOAuth (+https://twitteroauth.com)';

    /** @var array<mixed> Store proxy connection details */
    /**
     * @var array<mixed>
     */
    protected $proxy = [];

    /** @var bool Whether to encode the curl requests with gzip or not */
    protected $gzipEncoding = true;

    /**
     * Set the connection and response timeouts.
     *
     * @param  int  $connectionTimeout
     * @param  int  $timeout
     */
    public function setTimeouts($connectionTimeout, $timeout): void
    {
        $this->connectionTimeout = (int) $connectionTimeout;
        $this->timeout = (int) $timeout;
    }

    /**
     * @param  bool  $value
     */
    public function setDecodeJsonAsArray($value): void
    {
        $this->decodeJsonAsArray = (bool) $value;
    }

    /**
     * @param  string  $userAgent
     */
    public function setUserAgent($userAgent): void
    {
        $this->userAgent = (string) $userAgent;
    }

    /**
     * @param  array<mixed>  $proxy
     */
    public function setProxy(array $proxy): void
    {
        $this->proxy = $proxy;
    }

    /**
     * Whether to encode the curl requests with gzip or not.
     *
     * @param  bool  $gzipEncoding
     */
    public function setGzipEncoding($gzipEncoding): void
    {
        $this->gzipEncoding = (bool) $gzipEncoding;
    }
}
