<?php

namespace App\Streams\Exceptions;

use Throwable;

class PublishException extends RedisStreamException
{
    /**
     * Create a new Redis Stream publish exception instance.
     *
     * @param string $stream The stream where the publish failed
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $stream, string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $finalMessage = "Failed to publish message to stream '$stream'";
        if (!empty($message)) {
            $finalMessage .= ": $message";
        }
        
        parent::__construct($finalMessage, $code, $previous);
    }
}