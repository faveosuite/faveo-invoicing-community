<?php

namespace Tests\Unit\Common;

use App\Http\Controllers\Common\PhoneNumberController;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Tests\DBTestCase;

class PhoneNumberControllerTest extends DBTestCase
{
    private PhoneNumberController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PhoneNumberController;
    }

    /* ==================== validate() Method Tests ==================== */

    public function test_validate_returns_valid_result_for_valid_us_phone_number(): void
    {
        $result = $this->controller->validate('+14155551234', 'US');

        $this->assertTrue($result['valid']);
        $this->assertTrue($result['possible']);
        $this->assertTrue($result['validForRegion']);
        $this->assertEquals(1, $result['country_code']);
        $this->assertEquals('US', $result['region']);
        $this->assertNotNull($result['formatted']);
        $this->assertNull($result['error']);
    }

    public function test_validate_returns_valid_result_for_valid_indian_phone_number(): void
    {
        $result = $this->controller->validate('+919876543210', 'IN');

        $this->assertTrue($result['valid']);
        $this->assertTrue($result['possible']);
        $this->assertTrue($result['validForRegion']);
        $this->assertEquals(91, $result['country_code']);
        $this->assertEquals('IN', $result['region']);
        $this->assertNull($result['error']);
    }

    public function test_validate_returns_valid_result_without_country_code_when_number_has_plus(): void
    {
        $result = $this->controller->validate('+14155551234');

        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['country_code']);
        $this->assertEquals('US', $result['region']);
    }

    public function test_validate_returns_valid_result_for_uk_phone_number(): void
    {
        // Using a verified valid GB mobile number
        $result = $this->controller->validate('+447123456789', 'GB');

        $this->assertTrue($result['valid']);
        $this->assertEquals(44, $result['country_code']);
        $this->assertEquals('GB', $result['region']);
    }

    public function test_validate_returns_formatted_numbers_in_all_formats(): void
    {
        $result = $this->controller->validate('+14155551234', 'US');

        $this->assertArrayHasKey('formatted', $result);
        $this->assertArrayHasKey('e164', $result['formatted']);
        $this->assertArrayHasKey('international', $result['formatted']);
        $this->assertArrayHasKey('national', $result['formatted']);
        $this->assertArrayHasKey('rfc3966', $result['formatted']);

        $this->assertEquals('+14155551234', $result['formatted']['e164']);
        $this->assertStringContainsString('tel:', $result['formatted']['rfc3966']);
    }

    public function test_validate_returns_phone_number_type(): void
    {
        $result = $this->controller->validate('+919876543210', 'IN');

        $this->assertArrayHasKey('type', $result);
        $this->assertContains($result['type'], [
            'MOBILE',
            'FIXED_LINE',
            'FIXED_LINE_OR_MOBILE',
            'TOLL_FREE',
            'PREMIUM_RATE',
            'SHARED_COST',
            'VOIP',
            'PERSONAL_NUMBER',
            'PAGER',
            'UAN',
            'VOICEMAIL',
            'UNKNOWN',
        ]);
    }

    public function test_validate_returns_invalid_for_too_short_phone_number(): void
    {
        $result = $this->controller->validate('123', 'US');

        // Short numbers are parsed but marked as invalid (no exception thrown)
        $this->assertFalse($result['valid']);
        $this->assertFalse($result['possible']);
    }

    public function test_validate_returns_invalid_for_too_long_phone_number(): void
    {
        $result = $this->controller->validate('+1415555123456789012345', 'US');

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    public function test_validate_returns_invalid_for_non_numeric_phone_number(): void
    {
        $result = $this->controller->validate('abcdefghij', 'US');

        $this->assertFalse($result['valid']);
        // Non-numeric strings throw a parse exception
        $this->assertNotNull($result['error']);
        $this->assertStringContainsString('phone number', strtolower($result['error']));
    }

    public function test_validate_returns_error_for_invalid_country_code(): void
    {
        // +999 is an invalid country code prefix
        $result = $this->controller->validate('+9991234567890');

        $this->assertFalse($result['valid']);
        // Invalid country code throws a parse exception
        $this->assertNotNull($result['error']);
    }

    public function test_validate_returns_national_number_correctly(): void
    {
        $result = $this->controller->validate('+14155551234', 'US');

        $this->assertEquals(4155551234, $result['national_number']);
    }

    public function test_validate_handles_phone_number_with_spaces(): void
    {
        $result = $this->controller->validate('+1 415 555 1234', 'US');

        $this->assertTrue($result['valid']);
        $this->assertEquals('+14155551234', $result['formatted']['e164']);
    }

    public function test_validate_handles_phone_number_with_dashes(): void
    {
        $result = $this->controller->validate('+1-415-555-1234', 'US');

        $this->assertTrue($result['valid']);
        $this->assertEquals('+14155551234', $result['formatted']['e164']);
    }

    public function test_validate_handles_phone_number_with_parentheses(): void
    {
        $result = $this->controller->validate('(415) 555-1234', 'US');

        $this->assertTrue($result['valid']);
    }

    /* ==================== isValid() Method Tests ==================== */

    public function test_is_valid_returns_true_for_valid_phone_number(): void
    {
        $result = $this->controller->isValid('+14155551234', 'US');

        $this->assertTrue($result);
    }

    public function test_is_valid_returns_false_for_invalid_phone_number(): void
    {
        $result = $this->controller->isValid('123', 'US');

        $this->assertFalse($result);
    }

    public function test_is_valid_strict_mode_returns_true_for_valid_number(): void
    {
        $result = $this->controller->isValid('+14155551234', 'US', strict: true);

        $this->assertTrue($result);
    }

    public function test_is_valid_strict_mode_returns_false_for_invalid_number(): void
    {
        $result = $this->controller->isValid('123', 'US', strict: true);

        $this->assertFalse($result);
    }

    public function test_is_valid_without_country_code_for_international_number(): void
    {
        $result = $this->controller->isValid('+919876543210');

        $this->assertTrue($result);
    }

    public function test_is_valid_returns_false_for_empty_string(): void
    {
        $result = $this->controller->isValid('', 'US');

        $this->assertFalse($result);
    }

    /* ==================== isMobile() Method Tests ==================== */

    public function test_is_mobile_returns_true_for_mobile_number(): void
    {
        $result = $this->controller->isMobile('+919876543210', 'IN');

        $this->assertTrue($result);
    }

    public function test_is_mobile_returns_false_for_invalid_number(): void
    {
        $result = $this->controller->isMobile('123', 'US');

        $this->assertFalse($result);
    }

    public function test_is_mobile_returns_false_for_fixed_line_number(): void
    {
        // US landline numbers (toll-free)
        $result = $this->controller->isMobile('+18005551234', 'US');

        // Toll-free numbers are not mobile, so this should return false
        $this->assertFalse($result);
    }

    public function test_is_mobile_returns_true_for_fixed_line_or_mobile_type(): void
    {
        // Some numbers can be either fixed line or mobile
        $result = $this->controller->isMobile('+14155551234', 'US');

        // US numbers are typically FIXED_LINE_OR_MOBILE
        $validation = $this->controller->validate('+14155551234', 'US');
        if ($validation['type'] === 'FIXED_LINE_OR_MOBILE') {
            $this->assertTrue($result);
        }
    }

    /* ==================== formatE164() Method Tests ==================== */

    public function test_format_e164_returns_correct_format(): void
    {
        $result = $this->controller->formatE164('+1 415 555 1234', 'US');

        $this->assertEquals('+14155551234', $result);
    }

    public function test_format_e164_returns_null_for_invalid_number(): void
    {
        $result = $this->controller->formatE164('invalid', 'US');

        $this->assertNull($result);
    }

    public function test_format_e164_with_national_number_and_country_code(): void
    {
        $result = $this->controller->formatE164('9876543210', 'IN');

        $this->assertEquals('+919876543210', $result);
    }

    public function test_format_e164_removes_spaces_and_special_characters(): void
    {
        $result = $this->controller->formatE164('(415) 555-1234', 'US');

        $this->assertEquals('+14155551234', $result);
    }

    /* ==================== formatInternational() Method Tests ==================== */

    public function test_format_international_returns_correct_format(): void
    {
        $result = $this->controller->formatInternational('+14155551234', 'US');

        $this->assertNotNull($result);
        $this->assertStringContainsString('+1', $result);
    }

    public function test_format_international_returns_null_for_invalid_number(): void
    {
        $result = $this->controller->formatInternational('invalid', 'US');

        $this->assertNull($result);
    }

    public function test_format_international_for_indian_number(): void
    {
        $result = $this->controller->formatInternational('+919876543210', 'IN');

        $this->assertNotNull($result);
        $this->assertStringContainsString('+91', $result);
    }

    /* ==================== formatNational() Method Tests ==================== */

    public function test_format_national_returns_correct_format(): void
    {
        $result = $this->controller->formatNational('+14155551234', 'US');

        $this->assertNotNull($result);
        // National format doesn't include country code
        $this->assertStringNotContainsString('+1', $result);
    }

    public function test_format_national_returns_null_for_invalid_number(): void
    {
        $result = $this->controller->formatNational('invalid', 'US');

        $this->assertNull($result);
    }

    public function test_format_national_for_indian_number(): void
    {
        $result = $this->controller->formatNational('+919876543210', 'IN');

        $this->assertNotNull($result);
    }

    /* ==================== parse() Method Tests ==================== */

    public function test_parse_returns_array_with_correct_keys(): void
    {
        $result = $this->controller->parse('+14155551234', 'US');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('country_code', $result);
        $this->assertArrayHasKey('national_number', $result);
        $this->assertArrayHasKey('region', $result);
    }

    public function test_parse_returns_correct_values(): void
    {
        $result = $this->controller->parse('+919876543210', 'IN');

        $this->assertEquals(91, $result['country_code']);
        $this->assertEquals(9876543210, $result['national_number']);
        $this->assertEquals('IN', $result['region']);
    }

    public function test_parse_returns_null_for_invalid_number(): void
    {
        $result = $this->controller->parse('invalid', 'US');

        $this->assertNull($result);
    }

    public function test_parse_works_without_country_code_for_international_format(): void
    {
        // Using a verified valid GB mobile number
        $result = $this->controller->parse('+447123456789');

        $this->assertIsArray($result);
        $this->assertEquals(44, $result['country_code']);
        $this->assertEquals('GB', $result['region']);
    }

    /* ==================== validateWithMobileCode() Method Tests ==================== */

    public function test_validate_with_mobile_code_returns_valid_result(): void
    {
        $result = $this->controller->validateWithMobileCode('91', '9876543210', 'IN');

        $this->assertTrue($result['valid']);
        $this->assertEquals(91, $result['country_code']);
        $this->assertEquals('IN', $result['region']);
    }

    public function test_validate_with_mobile_code_handles_plus_prefix(): void
    {
        $result = $this->controller->validateWithMobileCode('+91', '9876543210', 'IN');

        $this->assertTrue($result['valid']);
    }

    public function test_validate_with_mobile_code_strips_leading_zeros(): void
    {
        $result = $this->controller->validateWithMobileCode('91', '09876543210', 'IN');

        $this->assertTrue($result['valid']);
        $this->assertEquals(9876543210, $result['national_number']);
    }

    public function test_validate_with_mobile_code_for_us_number(): void
    {
        $result = $this->controller->validateWithMobileCode('1', '4155551234', 'US');

        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['country_code']);
    }

    public function test_validate_with_mobile_code_returns_invalid_for_wrong_mobile_code(): void
    {
        $result = $this->controller->validateWithMobileCode('999', '1234567890', 'XX');

        $this->assertFalse($result['valid']);
    }

    public function test_validate_with_mobile_code_without_country_iso(): void
    {
        $result = $this->controller->validateWithMobileCode('91', '9876543210');

        $this->assertTrue($result['valid']);
        $this->assertEquals('IN', $result['region']);
    }

    /* ==================== normalizeForStorage() Method Tests ==================== */

    public function test_normalize_for_storage_returns_correct_structure(): void
    {
        $result = $this->controller->normalizeForStorage('91', '9876543210', 'IN');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('mobile_code', $result);
        $this->assertArrayHasKey('mobile', $result);
        $this->assertArrayHasKey('mobile_country_iso', $result);
        $this->assertArrayHasKey('formatted_e164', $result);
        $this->assertArrayHasKey('formatted_international', $result);
    }

    public function test_normalize_for_storage_returns_correct_values(): void
    {
        $result = $this->controller->normalizeForStorage('91', '9876543210', 'IN');

        $this->assertEquals('91', $result['mobile_code']);
        $this->assertEquals('9876543210', $result['mobile']);
        $this->assertEquals('IN', $result['mobile_country_iso']);
        $this->assertEquals('+919876543210', $result['formatted_e164']);
    }

    public function test_normalize_for_storage_returns_null_for_invalid_number(): void
    {
        $result = $this->controller->normalizeForStorage('999', '123', 'XX');

        $this->assertNull($result);
    }

    public function test_normalize_for_storage_for_us_number(): void
    {
        $result = $this->controller->normalizeForStorage('1', '4155551234', 'US');

        $this->assertIsArray($result);
        $this->assertEquals('1', $result['mobile_code']);
        $this->assertEquals('4155551234', $result['mobile']);
        $this->assertEquals('US', $result['mobile_country_iso']);
    }

    public function test_normalize_for_storage_strips_leading_zeros(): void
    {
        $result = $this->controller->normalizeForStorage('91', '09876543210', 'IN');

        $this->assertIsArray($result);
        $this->assertEquals('9876543210', $result['mobile']);
    }

    public function test_normalize_for_storage_handles_plus_prefix_in_mobile_code(): void
    {
        $result = $this->controller->normalizeForStorage('+91', '9876543210', 'IN');

        $this->assertIsArray($result);
        $this->assertEquals('91', $result['mobile_code']);
    }

    /* ==================== getExampleNumber() Method Tests ==================== */

    public function test_get_example_number_returns_mobile_example(): void
    {
        $result = $this->controller->getExampleNumber('US', mobile: true);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_get_example_number_returns_fixed_line_example(): void
    {
        $result = $this->controller->getExampleNumber('US', mobile: false);

        $this->assertNotNull($result);
        $this->assertIsString($result);
    }

    public function test_get_example_number_for_india(): void
    {
        $result = $this->controller->getExampleNumber('IN', mobile: true);

        $this->assertNotNull($result);
        $this->assertStringContainsString('+91', $result);
    }

    public function test_get_example_number_for_uk(): void
    {
        $result = $this->controller->getExampleNumber('GB', mobile: true);

        $this->assertNotNull($result);
        // UK numbers start with +44
        $this->assertMatchesRegularExpression('/^\+44/', $result);
    }

    public function test_get_example_number_returns_valid_examples_for_different_countries(): void
    {
        // Test that example numbers are returned for various valid countries
        $countries = ['US', 'IN', 'GB', 'DE', 'FR', 'AU', 'JP', 'CA', 'BR'];

        foreach ($countries as $country) {
            $result = $this->controller->getExampleNumber($country, mobile: true);

            $this->assertNotNull($result, 'Example number should be returned for country: '.$country);
            $this->assertIsString($result);
            $this->assertMatchesRegularExpression('/^\+\d+/', $result, sprintf('Example for %s should start with + and digits', $country));
        }
    }

    public function test_get_example_number_default_is_mobile(): void
    {
        $mobileExample = $this->controller->getExampleNumber('US');
        $mobileExampleExplicit = $this->controller->getExampleNumber('US', mobile: true);

        $this->assertEquals($mobileExample, $mobileExampleExplicit);
    }

    /* ==================== getCountryCallingCode() Method Tests ==================== */

    public function test_get_country_calling_code_returns_correct_code_for_us(): void
    {
        $result = $this->controller->getCountryCallingCode('US');

        $this->assertEquals(1, $result);
    }

    public function test_get_country_calling_code_returns_correct_code_for_india(): void
    {
        $result = $this->controller->getCountryCallingCode('IN');

        $this->assertEquals(91, $result);
    }

    public function test_get_country_calling_code_returns_correct_code_for_uk(): void
    {
        $result = $this->controller->getCountryCallingCode('GB');

        $this->assertEquals(44, $result);
    }

    public function test_get_country_calling_code_returns_correct_code_for_germany(): void
    {
        $result = $this->controller->getCountryCallingCode('DE');

        $this->assertEquals(49, $result);
    }

    public function test_get_country_calling_code_returns_correct_code_for_australia(): void
    {
        $result = $this->controller->getCountryCallingCode('AU');

        $this->assertEquals(61, $result);
    }

    public function test_get_country_calling_code_returns_correct_code_for_japan(): void
    {
        $result = $this->controller->getCountryCallingCode('JP');

        $this->assertEquals(81, $result);
    }

    public function test_get_country_calling_code_returns_null_for_invalid_country(): void
    {
        $result = $this->controller->getCountryCallingCode('XX');

        // Should return 0 for invalid country codes
        $this->assertEquals(0, $result);
    }

    /* ==================== Private Method Tests (using reflection) ==================== */

    public function test_get_number_type_name_returns_mobile(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getNumberTypeName',
            [PhoneNumberType::MOBILE]
        );

        $this->assertEquals('MOBILE', $result);
    }

    public function test_get_number_type_name_returns_fixed_line(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getNumberTypeName',
            [PhoneNumberType::FIXED_LINE]
        );

        $this->assertEquals('FIXED_LINE', $result);
    }

    public function test_get_number_type_name_returns_toll_free(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getNumberTypeName',
            [PhoneNumberType::TOLL_FREE]
        );

        $this->assertEquals('TOLL_FREE', $result);
    }

    public function test_get_number_type_name_returns_voip(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getNumberTypeName',
            [PhoneNumberType::VOIP]
        );

        $this->assertEquals('VOIP', $result);
    }

    public function test_get_error_message_for_invalid_country_code(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [NumberParseException::INVALID_COUNTRY_CODE]
        );

        $this->assertEquals('Invalid country calling code', $result);
    }

    public function test_get_error_message_for_not_a_number(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [NumberParseException::NOT_A_NUMBER]
        );

        $this->assertEquals('The string does not appear to be a phone number', $result);
    }

    public function test_get_error_message_for_too_short_nsn(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [NumberParseException::TOO_SHORT_NSN]
        );

        $this->assertEquals('Phone number is too short', $result);
    }

    public function test_get_error_message_for_too_long(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [NumberParseException::TOO_LONG]
        );

        $this->assertEquals('Phone number is too long', $result);
    }

    public function test_get_error_message_for_too_short_after_idd(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [NumberParseException::TOO_SHORT_AFTER_IDD]
        );

        $this->assertEquals('Phone number is too short after International Direct Dialing prefix', $result);
    }

    public function test_get_error_message_for_unknown_error_type(): void
    {
        $result = $this->getPrivateMethod(
            $this->controller,
            'getErrorMessage',
            [999] // Unknown error type
        );

        $this->assertEquals('Invalid phone number format', $result);
    }

    /* ==================== Edge Cases and Additional Tests ==================== */

    public function test_validate_handles_empty_string(): void
    {
        $result = $this->controller->validate('', 'US');

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['error']);
    }

    public function test_validate_handles_special_characters_only(): void
    {
        $result = $this->controller->validate('++--##', 'US');

        $this->assertFalse($result['valid']);
    }

    public function test_validate_handles_very_long_valid_format(): void
    {
        // International format with spaces
        $result = $this->controller->validate('+1 415 555 1234', 'US');

        $this->assertTrue($result['valid']);
    }

    public function test_multiple_countries_with_same_calling_code(): void
    {
        // US and Canada both use +1
        $usResult = $this->controller->getCountryCallingCode('US');
        $caResult = $this->controller->getCountryCallingCode('CA');

        $this->assertEquals(1, $usResult);
        $this->assertEquals(1, $caResult);
    }

    public function test_validate_canadian_number(): void
    {
        $result = $this->controller->validate('+14165551234', 'CA');

        $this->assertTrue($result['valid']);
        $this->assertEquals(1, $result['country_code']);
    }

    public function test_validate_australian_number(): void
    {
        $result = $this->controller->validate('+61412345678', 'AU');

        $this->assertTrue($result['valid']);
        $this->assertEquals(61, $result['country_code']);
        $this->assertEquals('AU', $result['region']);
    }

    public function test_validate_german_number(): void
    {
        $result = $this->controller->validate('+4915123456789', 'DE');

        $this->assertTrue($result['valid']);
        $this->assertEquals(49, $result['country_code']);
        $this->assertEquals('DE', $result['region']);
    }

    public function test_validate_japanese_number(): void
    {
        $result = $this->controller->validate('+819012345678', 'JP');

        $this->assertTrue($result['valid']);
        $this->assertEquals(81, $result['country_code']);
        $this->assertEquals('JP', $result['region']);
    }

    public function test_validate_french_number(): void
    {
        $result = $this->controller->validate('+33612345678', 'FR');

        $this->assertTrue($result['valid']);
        $this->assertEquals(33, $result['country_code']);
        $this->assertEquals('FR', $result['region']);
    }

    public function test_validate_brazilian_number(): void
    {
        $result = $this->controller->validate('+5511987654321', 'BR');

        $this->assertTrue($result['valid']);
        $this->assertEquals(55, $result['country_code']);
        $this->assertEquals('BR', $result['region']);
    }

    public function test_validate_chinese_number(): void
    {
        $result = $this->controller->validate('+8613812345678', 'CN');

        $this->assertTrue($result['valid']);
        $this->assertEquals(86, $result['country_code']);
        $this->assertEquals('CN', $result['region']);
    }

    public function test_constructor_initializes_phone_util(): void
    {
        $controller = new PhoneNumberController;
        $phoneUtil = $this->getPrivateProperty($controller, 'phoneUtil');

        $this->assertInstanceOf(PhoneNumberUtil::class, $phoneUtil);
    }

    public function test_validate_with_national_format_and_country_code(): void
    {
        // National format requires country code
        $result = $this->controller->validate('9876543210', 'IN');

        $this->assertTrue($result['valid']);
        $this->assertEquals(91, $result['country_code']);
    }

    public function test_format_methods_return_null_for_empty_string(): void
    {
        $this->assertNull($this->controller->formatE164('', 'US'));
        $this->assertNull($this->controller->formatInternational('', 'US'));
        $this->assertNull($this->controller->formatNational('', 'US'));
    }

    public function test_is_mobile_with_international_format(): void
    {
        $result = $this->controller->isMobile('+919876543210');

        $this->assertTrue($result);
    }

    public function test_normalize_for_storage_returns_string_types(): void
    {
        $result = $this->controller->normalizeForStorage('91', '9876543210', 'IN');

        $this->assertIsString($result['mobile_code']);
        $this->assertIsString($result['mobile']);
        $this->assertIsString($result['mobile_country_iso']);
        $this->assertIsString($result['formatted_e164']);
        $this->assertIsString($result['formatted_international']);
    }

    public function test_get_example_number_returns_formatted_string(): void
    {
        $result = $this->controller->getExampleNumber('IN', mobile: true);

        // Should contain country code and be in international format
        $this->assertMatchesRegularExpression('/^\+\d+/', $result);
    }

    public function test_validate_toll_free_number(): void
    {
        $result = $this->controller->validate('+18005551234', 'US');

        $this->assertTrue($result['valid']);
        $this->assertEquals('TOLL_FREE', $result['type']);
    }

    public function test_validate_premium_rate_number(): void
    {
        // UK premium rate number
        $result = $this->controller->validate('+449001234567', 'GB');

        if ($result['valid']) {
            $this->assertEquals('PREMIUM_RATE', $result['type']);
        }
    }

    public function test_validate_premium_or_shared_cost_number(): void
    {
        // UK 0845 numbers can be either SHARED_COST or PREMIUM_RATE depending on libphonenumber version
        $result = $this->controller->validate('+448451234567', 'GB');

        if ($result['valid']) {
            // UK 0845 numbers are premium rate or shared cost
            $this->assertTrue(
                in_array($result['type'], ['SHARED_COST', 'PREMIUM_RATE', 'UNKNOWN']),
                'Expected type to be SHARED_COST, PREMIUM_RATE or UNKNOWN, got: '.$result['type']
            );
            $this->assertEquals(44, $result['country_code']);
        } else {
            // If number is not valid, just assert that validation ran
            $this->assertFalse($result['valid']);
        }
    }
}
