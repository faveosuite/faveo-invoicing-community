<?php

namespace App\Plugins\Zoho\Controllers\Exceptions;

class ZohoApiException extends \Exception
{
    public function __construct(
        protected string $errorId,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function getErrorId(): string
    {
        return $this->errorId;
    }
}
