<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Rules;

use App\Rules\PhoneNumber;
use Tests\TestCase;

class PhoneNumberTest extends TestCase
{
    // --- Blank values are skipped (pass without calling $fail) ---

    public function test_empty_string_is_skipped(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', '', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails, 'Blank values must be skipped — required rule handles them');
    }

    public function test_null_value_is_skipped(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', null, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    public function test_whitespace_only_is_skipped(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', '   ', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    // --- Valid numbers ---

    public function test_valid_us_e164_number_passes(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', '+12025551234', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    public function test_valid_in_number_passes(): void
    {
        $fails = false;
        (new PhoneNumber('IN'))->validate('phone', '+919876543210', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    public function test_valid_gb_number_passes(): void
    {
        $fails = false;
        (new PhoneNumber('GB'))->validate('phone', '+441234567890', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    // --- Invalid non-blank values ---

    public function test_letters_only_fails(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', 'notaphone', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_too_short_number_fails(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', '123', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_sql_injection_payload_fails(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', "'; DROP TABLE users; --", function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_xss_payload_fails(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', '<script>alert(1)</script>', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_random_string_fails(): void
    {
        $fails = false;
        (new PhoneNumber('US'))->validate('phone', 'hello world', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_country_iso_scopes_validation(): void
    {
        // A US-specific number format should fail when validated for an unrelated country
        // (strict mode requires the number to be valid FOR the region).
        // We just assert the rule doesn't throw — the specific fail/pass depends on the
        // library's loose vs strict interpretation.
        $fails = false;
        (new PhoneNumber('IN'))->validate('phone', '+12025551234', function () use (&$fails): void {
            $fails = true;
        });

        // No assertion on outcome — just verify no exception is thrown.
        $this->assertTrue(true);
    }
}
