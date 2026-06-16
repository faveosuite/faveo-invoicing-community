<?php

namespace App\Plugins\Mailchimp\Exceptions;

use Throwable;
use RuntimeException;

class MailchimpApiException extends RuntimeException
{
    public function __construct(string $message = '', private readonly int $httpStatus = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $httpStatus, $previous);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function isAuthError(): bool
    {
        return $this->httpStatus === 401;
    }

    public function isMemberExists(): bool
    {
        return $this->httpStatus === 400 && str_contains($this->getMessage(), 'already a list member');
    }
}
