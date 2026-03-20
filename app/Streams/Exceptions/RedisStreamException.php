<?php

namespace App\Streams\Exceptions;

use Exception;
use Throwable;

class RedisStreamException extends Exception
{
    /**
     * Create a new Redis Stream exception instance.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(string $message = 'Redis Stream error', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
