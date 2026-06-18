<?php

namespace App\Http\Controllers\Common;

use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

class PhoneNumberController
{
    private readonly PhoneNumberUtil $phoneUtil;

    public function __construct()
    {
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    /**
     * Parse and validate a phone number.
     *
     * @param  string  $phoneNumber  The phone number to validate
     * @param  string|null  $countryCode  The two-letter country code (e.g., 'US', 'IN')
     * @return array Contains a validation result and parsed info
     */
    public function validate(string $phoneNumber, ?string $countryCode = null): array
    {
        try {
            // If the phone number starts with +, no country code needed for parsing
            $parsedNumber = $this->phoneUtil->parse($phoneNumber, $countryCode);
            $isValid = $this->phoneUtil->isValidNumber($parsedNumber);
            $isPossible = $this->phoneUtil->isPossibleNumber($parsedNumber);
            $region = $this->phoneUtil->getRegionCodeForNumber($parsedNumber);
            $isValidNumberForRegion = $this->phoneUtil
                ->isValidNumberForRegion($parsedNumber, $region);

            return [
                'valid' => $isValid,
                'possible' => $isPossible,
                'validForRegion' => $isValidNumberForRegion,
                'type' => $this->getNumberTypeName($this->phoneUtil->getNumberType($parsedNumber)),
                'country_code' => $parsedNumber->getCountryCode(),
                'national_number' => $parsedNumber->getNationalNumber(),
                'region' => $region,
                'formatted' => [
                    'e164' => $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::E164),
                    'international' => $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::INTERNATIONAL),
                    'national' => $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::NATIONAL),
                    'rfc3966' => $this->phoneUtil->format($parsedNumber, PhoneNumberFormat::RFC3966),
                ],
                'error' => null,
            ];
        } catch (NumberParseException $numberParseException) {
            return [
                'valid' => false,
                'possible' => false,
                'validForRegion' => false,
                'type' => null,
                'country_code' => null,
                'national_number' => null,
                'region' => null,
                'formatted' => null,
                'error' => $this->getErrorMessage($numberParseException->getErrorType()),
            ];
        }
    }

    /**
     * Check if a phone number is valid.
     */
    public function isValid(string $phoneNumber, ?string $countryCode = null, bool $strict = false): bool
    {
        $res = $this->validate($phoneNumber, $countryCode);

        if ($res['valid']) {
            return true;
        }

        if ($strict) {
            return $res['possible'] && $res['validForRegion'];
        }

        return false;
    }

    /**
     * Check if a phone number is a mobile number.
     */
    public function isMobile(string $phoneNumber, ?string $countryCode = null): bool
    {
        $result = $this->validate($phoneNumber, $countryCode);

        if (! $result['valid']) {
            return false;
        }

        return in_array($result['type'], ['MOBILE', 'FIXED_LINE_OR_MOBILE']);
    }

    /**
     * Format phone number to E.164 format (+14155551234).
     */
    public function formatE164(string $phoneNumber, ?string $countryCode = null): ?string
    {
        $result = $this->validate($phoneNumber, $countryCode);

        return $result['formatted']['e164'] ?? null;
    }

    /**
     * Format phone number to international format (+1 415-555-1234).
     */
    public function formatInternational(string $phoneNumber, ?string $countryCode = null): ?string
    {
        $result = $this->validate($phoneNumber, $countryCode);

        return $result['formatted']['international'] ?? null;
    }

    /**
     * Format phone number to national format ((415) 555-1234).
     */
    public function formatNational(string $phoneNumber, ?string $countryCode = null): ?string
    {
        $result = $this->validate($phoneNumber, $countryCode);

        return $result['formatted']['national'] ?? null;
    }

    /**
     * Get parsed phone number details (country code, national number, region).
     */
    public function parse(string $phoneNumber, ?string $countryCode = null): ?array
    {
        $result = $this->validate($phoneNumber, $countryCode);

        if (! $result['valid']) {
            return null;
        }

        return [
            'country_code' => $result['country_code'],
            'national_number' => $result['national_number'],
            'region' => $result['region'],
        ];
    }

    /**
     * Validate phone number with mobile code (e.g., mobile_code=91, mobile=9876543210, country=IN)
     * This is specifically designed for your app's data structure.
     *
     * @param  string  $mobileCode  Country calling code (e.g., '91', '1')
     * @param  string  $mobile  The national number without country code
     * @param  string|null  $countryIso  Two-letter country code (e.g., 'IN', 'US')
     */
    public function validateWithMobileCode(string $mobileCode, string $mobile, ?string $countryIso = null): array
    {
        // Remove any leading zeros from the mobile number
        $mobile = ltrim($mobile, '0');

        // Construct a full phone number with + prefix
        $fullNumber = '+'.ltrim($mobileCode, '+').$mobile;

        return $this->validate($fullNumber, $countryIso);
    }

    /**
     * Format and normalize phone number for storage
     * Returns an array with properly formatted mobile_code and mobile fields.
     *
     * @return array|null Returns null if invalid
     */
    public function normalizeForStorage(string $mobileCode, string $mobile, ?string $countryIso = null): ?array
    {
        $result = $this->validateWithMobileCode($mobileCode, $mobile, $countryIso);

        if (! $result['valid']) {
            return null;
        }

        return [
            'mobile_code' => (string) $result['country_code'],
            'mobile' => (string) $result['national_number'],
            'mobile_country_iso' => $result['region'],
            'formatted_e164' => $result['formatted']['e164'],
            'formatted_international' => $result['formatted']['international'],
        ];
    }

    /**
     * Get the phone number type name.
     */
    private function getNumberTypeName(int|PhoneNumberType $type): string
    {
        // Handle both enum and integer types (different libphonenumber versions)
        if ($type instanceof PhoneNumberType) {
            return $type->name;
        }

        return PhoneNumberType::tryFrom($type)?->name ?? 'UNKNOWN';
    }

    /**
     * Get human-readable error message.
     */
    private function getErrorMessage(int $errorType): string
    {
        return match ($errorType) {
            NumberParseException::INVALID_COUNTRY_CODE => 'Invalid country calling code',
            NumberParseException::NOT_A_NUMBER => 'The string does not appear to be a phone number',
            NumberParseException::TOO_SHORT_AFTER_IDD => 'Phone number is too short after International Direct Dialing prefix',
            NumberParseException::TOO_SHORT_NSN => 'Phone number is too short',
            NumberParseException::TOO_LONG => 'Phone number is too long',
            default => 'Invalid phone number format',
        };
    }

    /**
     * Get example number for a country.
     *
     * @param  string  $countryCode  Two-letter country code
     * @param  bool  $mobile  Get mobile example (true) or fixed line example (false)
     */
    public function getExampleNumber(string $countryCode, bool $mobile = true): ?string
    {
        try {
            $type = $mobile ? PhoneNumberType::MOBILE : PhoneNumberType::FIXED_LINE;
            $example = $this->phoneUtil->getExampleNumberForType($countryCode, $type);

            return $this->phoneUtil->format($example, PhoneNumberFormat::INTERNATIONAL);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Get country calling code for a country.
     *
     * @param  string  $countryCode  Two-letter country code
     */
    public function getCountryCallingCode(string $countryCode): ?int
    {
        try {
            return $this->phoneUtil->getCountryCodeForRegion($countryCode);
        } catch (Exception) {
            return null;
        }
    }
}
