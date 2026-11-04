<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Rules;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Tests\TestCase;

/**
 * The app-wide password policy: Laravel's Password::defaults() as set in AppServiceProvider
 * (8–64 chars, upper + lower case, a number, a symbol).
 */
class PasswordPolicyTest extends TestCase
{
    private function fails(string $value): bool
    {
        return Validator::make(['password' => $value], ['password' => [Password::defaults()]])->fails();
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

    public function test_password_at_maximum_length_64_passes(): void
    {
        $this->assertFalse($this->fails('Secure@1'.str_repeat('x', 56))); // exactly 64 chars
    }

    public function test_password_with_various_specials_passes(): void
    {
        // Any non-alphanumeric symbol counts, incl. ones password managers use (&, ^, -, ...).
        foreach (['~', '*', '!', '@', '$', '#', '%', '_', '+', '.', '?', ':', ',', '&', '^', '-', '(', ')', '/', '"'] as $special) {
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

    public function test_too_long_65_chars_fails(): void
    {
        $this->assertTrue($this->fails('Secure@1'.str_repeat('x', 57)));
    }

    public function test_unicode_letters_are_allowed(): void
    {
        $this->assertFalse($this->fails('Sécure@Pass1'));
    }
}
