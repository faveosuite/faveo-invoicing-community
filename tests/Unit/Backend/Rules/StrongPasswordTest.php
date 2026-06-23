<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Rules;

use App\Rules\StrongPassword;
use Tests\TestCase;

/**
 * Pattern: ^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[~*!@$#%_+.?:,{ }])[A-Za-z\d~*!@$#%_+.?:,{ }]{8,16}$
 * Requires: uppercase, lowercase, digit, one of [~*!@$#%_+.?:,{ }], length 8–16.
 */
class StrongPasswordTest extends TestCase
{
    private StrongPassword $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new StrongPassword();
    }

    private function fails(string $value): bool
    {
        $failed = false;
        $this->rule->validate('password', $value, function () use (&$failed): void {
            $failed = true;
        });

        return $failed;
    }

    // --- Valid passwords ---

    public function test_password_meeting_all_requirements_passes(): void
    {
        $this->assertFalse($this->fails('Secure@Password1'));
    }

    public function test_password_at_minimum_length_8_passes(): void
    {
        $this->assertFalse($this->fails('Secure@1'));
    }

    public function test_password_at_maximum_length_16_passes(): void
    {
        $this->assertFalse($this->fails('Secure@Pass1Word')); // exactly 16 chars
    }

    public function test_password_with_various_specials_passes(): void
    {
        foreach (['~', '*', '!', '@', '$', '#', '%', '_', '+', '.', '?', ':', ','] as $special) {
            $this->assertFalse($this->fails("SecureA1{$special}"), "Failed with special: $special");
        }
    }

    // --- Invalid passwords ---

    public function test_empty_string_fails(): void
    {
        $this->assertTrue($this->fails(''));
    }

    public function test_whitespace_only_fails(): void
    {
        $this->assertTrue($this->fails('        '));
    }

    public function test_no_uppercase_fails(): void
    {
        $this->assertTrue($this->fails('secure@password1'));
    }

    public function test_no_lowercase_fails(): void
    {
        $this->assertTrue($this->fails('SECURE@PASSWORD1'));
    }

    public function test_no_digit_fails(): void
    {
        $this->assertTrue($this->fails('Secure@Password'));
    }

    public function test_no_special_character_fails(): void
    {
        $this->assertTrue($this->fails('SecurePassword1'));
    }

    public function test_too_short_7_chars_fails(): void
    {
        $this->assertTrue($this->fails('Sec@r1x'));
    }

    public function test_too_long_17_chars_fails(): void
    {
        $this->assertTrue($this->fails('Secure@Pass1WordX!'));
    }

    public function test_unicode_outside_allowed_charset_fails(): void
    {
        // The pattern only allows ASCII chars in [A-Za-z\d~*!@$#%_+.?:,{ }]
        $this->assertTrue($this->fails('Sécure@Pass1'));
    }

    
}
