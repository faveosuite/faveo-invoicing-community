<?php

namespace App\Streams\Exceptions;

use Throwable;

class ConnectionException extends RedisStreamException
{
    /**
     * Create a new Redis Stream connection exception instance.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "Redis connection error", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}