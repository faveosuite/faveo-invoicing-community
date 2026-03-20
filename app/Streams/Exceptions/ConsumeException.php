<?php

namespace App\Streams\Exceptions;

use Throwable;

class ConsumeException extends RedisStreamException
{
    /**
     * Create a new Redis Stream consume exception instance.
     *
     * @param string $stream The stream where the consume operation failed
     * @param string $group The consumer group
     * @param string $consumer The consumer name
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $stream, 
        string $group, 
        string $consumer, 
        string $message = "", 
        int $code = 0, 
        ?Throwable $previous = null
    ) {
        $finalMessage = "Failed to consume messages from stream '$stream' (group: $group, consumer: $consumer)";
        if (!empty($message)) {
            $finalMessage .= ": $message";
        }
        
        parent::__construct($finalMessage, $code, $previous);
    }
}