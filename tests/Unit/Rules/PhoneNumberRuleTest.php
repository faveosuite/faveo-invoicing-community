<?php

declare(strict_types=1);

namespace Tests\Unit\Rules;

use App\Rules\PhoneNumber;
use Illuminate\Support\Facades\Validator;
use Tests\DBTestCase;

class PhoneNumberRuleTest extends DBTestCase
{
    /* ==================== Constructor Tests ==================== */

    public function test_rule_can_be_instantiated_with_country_iso(): void
    {
        $rule = new PhoneNumber('US');

        $this->assertInstanceOf(PhoneNumber::class, $rule);
    }

    public function test_rule_stores_mobile_country_iso(): void
    {
        $rule = new PhoneNumber('IN');

        $mobileCountryIso = $this->getPrivateProperty($rule, 'mobileCountryIso');

        $this->assertEquals('IN', $mobileCountryIso);
    }

    /* ==================== Valid Phone Number Tests ==================== */

    public function test_passes_for_valid_us_phone_number(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+14155551234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_valid_indian_phone_number(): void
    {
        $rule = new PhoneNumber('IN');

        $validator = Validator::make(
            ['phone' => '+919876543210'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_valid_uk_phone_number(): void
    {
        $rule = new PhoneNumber('GB');

        $validator = Validator::make(
            ['phone' => '+447123456789'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_valid_german_phone_number(): void
    {
        $rule = new PhoneNumber('DE');

        $validator = Validator::make(
            ['phone' => '+4915123456789'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_valid_australian_phone_number(): void
    {
        $rule = new PhoneNumber('AU');

        $validator = Validator::make(
            ['phone' => '+61412345678'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_valid_national_format_with_country_iso(): void
    {
        $rule = new PhoneNumber('IN');

        $validator = Validator::make(
            ['phone' => '9876543210'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_phone_with_spaces(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+1 415 555 1234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_phone_with_dashes(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+1-415-555-1234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_phone_with_parentheses(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '(415) 555-1234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    /* ==================== Blank/Empty Value Tests ==================== */

    public function test_passes_for_null_value(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => null],
            ['phone' => $rule]
        );

        // Blank values should pass (no validation error)
        $this->assertTrue($validator->passes());
    }

    public function test_passes_for_empty_string(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => ''],
            ['phone' => $rule]
        );

        // Blank values should pass (no validation error)
        $this->assertTrue($validator->passes());
    }

    /* ==================== Invalid Phone Number Tests ==================== */

    public function test_fails_for_too_short_phone_number(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '123'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_for_too_long_phone_number(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+1415555123456789012345'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_for_non_numeric_phone_number(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => 'abcdefghij'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_for_invalid_country_code_mismatch(): void
    {
        // Phone number is valid for IN but we're validating against US
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '9876543210'],  // Valid Indian format, but we're checking US
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_for_random_special_characters(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '++--##@@'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_for_letters_mixed_with_numbers(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+1abc5551234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    /* ==================== Error Message Tests ==================== */

    public function test_returns_correct_error_message_for_invalid_phone(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => 'invalid'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('phone'));
    }

    public function test_error_message_contains_attribute_name(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['mobile_number' => 'invalid'],
            ['mobile_number' => $rule]
        );

        $this->assertTrue($validator->fails());
        $errors = $validator->errors();
        $this->assertTrue($errors->has('mobile_number'));
    }

    /* ==================== Different Country ISO Tests ==================== */

    public function test_validates_correctly_for_canadian_number(): void
    {
        $rule = new PhoneNumber('CA');

        $validator = Validator::make(
            ['phone' => '+14165551234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_correctly_for_french_number(): void
    {
        $rule = new PhoneNumber('FR');

        $validator = Validator::make(
            ['phone' => '+33612345678'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_correctly_for_japanese_number(): void
    {
        $rule = new PhoneNumber('JP');

        $validator = Validator::make(
            ['phone' => '+819012345678'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_correctly_for_brazilian_number(): void
    {
        $rule = new PhoneNumber('BR');

        $validator = Validator::make(
            ['phone' => '+5511987654321'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_correctly_for_chinese_number(): void
    {
        $rule = new PhoneNumber('CN');

        $validator = Validator::make(
            ['phone' => '+8613812345678'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    /* ==================== Edge Cases ==================== */

    public function test_handles_whitespace_only_input(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '   '],
            ['phone' => $rule]
        );

        // Whitespace is treated as blank so validation should pass
        $this->assertTrue($validator->passes());
    }

    public function test_validates_toll_free_number(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+18005551234'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_fails_for_incomplete_phone_number(): void
    {
        $rule = new PhoneNumber('IN');

        $validator = Validator::make(
            ['phone' => '+9198765'],  // Incomplete Indian number
            ['phone' => $rule]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_validates_number_with_country_calling_code(): void
    {
        $rule = new PhoneNumber('IN');

        $validator = Validator::make(
            ['phone' => '+919876543210'],
            ['phone' => $rule]
        );

        $this->assertTrue($validator->passes());
    }

    /* ==================== Validator Integration Tests ==================== */

    public function test_works_with_other_validation_rules(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+14155551234'],
            ['phone' => ['required', $rule]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_required_rule_fails_with_empty_phone(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => ''],
            ['phone' => ['required', $rule]]
        );

        // Should fail because of 'required' rule, not the PhoneNumber rule
        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('phone'));
    }

    public function test_validates_multiple_phone_fields(): void
    {
        $usRule = new PhoneNumber('US');
        $inRule = new PhoneNumber('IN');

        $validator = Validator::make(
            [
                'us_phone' => '+14155551234',
                'in_phone' => '+919876543210',
            ],
            [
                'us_phone' => $usRule,
                'in_phone' => $inRule,
            ]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_multiple_phone_fields_with_one_invalid(): void
    {
        $usRule = new PhoneNumber('US');
        $inRule = new PhoneNumber('IN');

        $validator = Validator::make(
            [
                'us_phone' => '+14155551234',
                'in_phone' => 'invalid',
            ],
            [
                'us_phone' => $usRule,
                'in_phone' => $inRule,
            ]
        );

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->errors()->has('us_phone'));
        $this->assertTrue($validator->errors()->has('in_phone'));
    }

    /* ==================== Strict Mode Tests ==================== */

    public function test_uses_strict_validation_mode(): void
    {
        // The rule uses strict mode (third parameter = true in isValid)
        // This means the number must be both possible AND valid for the region
        $rule = new PhoneNumber('US');

        // A number that might be "possible" but not "valid for region"
        $validator = Validator::make(
            ['phone' => '1234567890'],  // 10 digits without proper area code
            ['phone' => $rule]
        );

        // In strict mode, this should fail
        $this->assertTrue($validator->fails());
    }

    /* ==================== Real-World Scenario Tests ==================== */

    public function test_form_registration_phone_validation(): void
    {
        // Simulating a form registration with phone validation
        $rule = new PhoneNumber('IN');

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+919876543210',
        ];

        $validator = Validator::make(
            $formData,
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email'],
                'phone' => ['required', $rule],
            ]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_form_registration_fails_with_invalid_phone(): void
    {
        $rule = new PhoneNumber('IN');

        $formData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => 'not-a-phone',
        ];

        $validator = Validator::make(
            $formData,
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email'],
                'phone' => ['required', $rule],
            ]
        );

        $this->assertTrue($validator->fails());
        $this->assertFalse($validator->errors()->has('name'));
        $this->assertFalse($validator->errors()->has('email'));
        $this->assertTrue($validator->errors()->has('phone'));
    }

    public function test_optional_phone_field(): void
    {
        $rule = new PhoneNumber('US');

        // Phone is optional (nullable), so empty should pass
        $validator = Validator::make(
            ['phone' => null],
            ['phone' => ['nullable', $rule]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_optional_phone_field_with_valid_value(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => '+14155551234'],
            ['phone' => ['nullable', $rule]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_optional_phone_field_with_invalid_value(): void
    {
        $rule = new PhoneNumber('US');

        $validator = Validator::make(
            ['phone' => 'invalid-phone'],
            ['phone' => ['nullable', $rule]]
        );

        $this->assertTrue($validator->fails());
    }
}
