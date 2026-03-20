<?php

namespace App\Streams\Exceptions;

use Throwable;

class MessageProcessingException extends RedisStreamException
{
    /**
     * Create a new message processing exception instance.
     *
     * @param  string  $stream  The stream name
     * @param  string  $messageId  The message ID that failed to process
     * @param  int  $attempt  The processing attempt number
     * @param  string  $message
     * @param  int  $code
     * @param  Throwable|null  $previous
     */
    public function __construct(
        public readonly string $stream,
        public readonly string $messageId,
        public readonly int $attempt = 1,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $finalMessage = "Failed to process message $messageId from stream '$stream' (attempt $attempt)";
        if (! empty($message)) {
            $finalMessage .= ": $message";
        }

        parent::__construct($finalMessage, $code, $previous);
    }
}
