<?php

namespace App\Plugins\Zoho\Integrations\Crm\Controllers\Exceptions;

use Exception;

class ZohoCrmApiException extends Exception
{
    protected array $error;
    protected int $httpStatus;

    public function __construct(
        string $message,
        array $error = [],
        int $httpStatus = 400
    ) {
        parent::__construct($message, $httpStatus);
        $this->error = $error;
        $this->httpStatus = $httpStatus;
    }

    public static function fromResponse(array $response, int $httpStatus = 400): self
    {
        $code = $response['code'] ?? 'UNKNOWN_ERROR';

        return new self(
            self::humanMessage($code, $response),
            $response,
            $httpStatus
        );
    }

    public function getZohoCode(): ?string
    {
        return $this->error['code'] ?? null;
    }

    public function getZohoDetails(): array
    {
        return $this->error['details'] ?? [];
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Convert Zoho error codes into developer-friendly messages.
     */
    protected static function humanMessage(string $code, array $error): string
    {
        return match ($code) {
            // ─────────── MODULE ERRORS ───────────
            'INVALID_MODULE' => 'Invalid or unsupported Zoho module name',

            // ─────────── AUTH / PERMISSION ───────────
            'OAUTH_SCOPE_MISMATCH' => 'OAuth scope missing for this module operation',

            'NO_PERMISSION',
            'AUTHORIZATION_FAILED' => 'You do not have permission to perform this operation',

            // ─────────── VALIDATION ───────────
            'MANDATORY_NOT_FOUND' => 'Required field missing: '.
                ($error['details']['api_name'] ?? 'unknown'),

            'INVALID_DATA' => 'Invalid data provided to Zoho CRM',

            'DEPENDENT_FIELD_MISSING' => 'Dependent field missing or invalid',

            'DEPENDENT_MISMATCH' => 'Dependent field or module mismatch',

            'DUPLICATE_DATA' => 'Duplicate value for unique field',

            'MULTIPLE_OR_MULTI_ERRORS' => 'Duplicate data found in multiple fields',

            // ─────────── LIMITS ───────────
            'LIMIT_EXCEEDED' => 'Zoho CRM API limit exceeded',

            // ─────────── RECORD STATE ───────────
            'RECORD_LOCKED' => 'Record is locked and cannot be modified',

            // ─────────── REQUEST / URL ───────────
            'INVALID_REQUEST_METHOD' => 'Invalid HTTP method used for Zoho API',

            'INVALID_URL_PATTERN' => 'Invalid Zoho API URL',

            // ─────────── SERVER ───────────
            'INTERNAL_ERROR' => 'Zoho CRM internal server error',

            // ─────────── FALLBACK ───────────
            default => "Zoho CRM Error [$code]: ".($error['message'] ?? 'Unknown error'),
        };
    }
}
