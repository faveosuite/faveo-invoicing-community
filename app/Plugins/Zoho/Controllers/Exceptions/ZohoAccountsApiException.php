<?php

declare(strict_types=1);

namespace App\Plugins\Zoho\Controllers\Exceptions;

class ZohoAccountsApiException extends ZohoApiException
{
    public static function invalidClient(): ZohoAccountsApiException
    {
        return new ZohoAccountsApiException(
            'invalid_client',
            'Check your client_id and datacenter location'
        );
    }

    public static function invalidClientSecret(): ZohoAccountsApiException
    {
        return new ZohoAccountsApiException(
            'invalid_client_secret',
            'client_secret parameter is missing or invalid'
        );
    }

    public static function invalidCode(): ZohoAccountsApiException
    {
        return new ZohoAccountsApiException(
            'invalid_code',
            'authorization code is invalid or expired'
        );
    }
}
