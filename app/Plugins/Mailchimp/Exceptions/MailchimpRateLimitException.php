<?php

namespace App\Plugins\Mailchimp\Exceptions;

use Throwable;

class MailchimpRateLimitException extends MailchimpApiException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Mailchimp API rate limit exceeded. Try again later.', 429, $previous);
    }
}
