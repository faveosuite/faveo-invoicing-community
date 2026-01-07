<?php

namespace App\Plugins\Zoho;

use Illuminate\Support\Facades\Log;

class NullDriver
{
    public function __construct(protected bool $logCalls = false)
    {
    }

    public function __call(string $name, array $arguments): void
    {
        if ($this->logCalls) {
            Log::debug(sprintf('Called Campaigns::%s', $name), $arguments);
        }
    }
}
