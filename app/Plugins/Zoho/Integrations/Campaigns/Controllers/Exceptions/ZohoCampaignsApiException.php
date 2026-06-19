<?php

namespace App\Plugins\Zoho\Integrations\Campaigns\Controllers\Exceptions;

use App\Plugins\Zoho\Controllers\Exceptions\ZohoApiException;

class ZohoCampaignsApiException extends ZohoApiException
{
    /**
     * @param array<mixed> $response
     */
    public static function fromResponse(array $response): static
    {
        return new static( // @phpstan-ignore new.static
            errorId: (string) ($response['code'] ?? ''),
            message: $response['message'] ?? 'An error occurred',
        );
    }
}
