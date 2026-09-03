<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Rules;

use App\Rules\Honeypot;
use Crypt;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
    private Honeypot $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new Honeypot(minTime: 0); // minTime=0 so time check passes immediately
    }

    private function validPayload(?string $potValue = '', int $secondsAgo = 1): array
    {
        return [
            'pAbcXyz' => $potValue,
            'tAbcXyz' => Crypt::encrypt(time() - $secondsAgo),
        ];
    }

    // --- Valid submissions ---

    public function test_valid_payload_with_empty_pot_passes(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', $this->validPayload(''), function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    public function test_valid_payload_with_null_pot_passes(): void
    {
        $fails = false;
        $payload = $this->validPayload(null);

        $this->rule->validate('honeypot', $payload, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    // --- Invalid: wrong structure ---

    public function test_null_value_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', null, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_empty_string_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', '', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_non_array_string_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', 'bot-filled', function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_array_with_one_element_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', ['pXxx' => ''], function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_array_with_three_elements_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', ['pXxx' => '', 'tYyy' => Crypt::encrypt(time()), 'extra' => 'x'], function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    // --- Invalid: pot field filled ---

    public function test_filled_pot_field_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', $this->validPayload('bot-filled-this'), function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_whitespace_in_pot_field_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', $this->validPayload('   '), function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_xss_payload_in_pot_fails(): void
    {
        $fails = false;
        $this->rule->validate('honeypot', $this->validPayload('<script>alert(1)</script>'), function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_numeric_zero_string_in_pot_fails(): void
    {
        // '0' is not '' and not null — treated as filled
        $fails = false;
        $this->rule->validate('honeypot', $this->validPayload('0'), function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    // --- Invalid: time field problems ---

    public function test_invalid_encrypted_time_fails(): void
    {
        $fails = false;
        $payload = ['pAbcXyz' => '', 'tAbcXyz' => 'not-encrypted'];
        $this->rule->validate('honeypot', $payload, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_empty_time_field_fails(): void
    {
        $fails = false;
        $payload = ['pAbcXyz' => '', 'tAbcXyz' => ''];
        $this->rule->validate('honeypot', $payload, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_null_time_field_fails(): void
    {
        $fails = false;
        $payload = ['pAbcXyz' => '', 'tAbcXyz' => null];
        $this->rule->validate('honeypot', $payload, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    public function test_future_timestamp_fails_with_min_time_greater_than_zero(): void
    {
        // minTime=5: form must be at least 5 seconds old. "just now" fails.
        $rule = new Honeypot(minTime: 5);
        $payload = ['pAbcXyz' => '', 'tAbcXyz' => Crypt::encrypt(time())];

        $fails = false;
        $rule->validate('honeypot', $payload, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertTrue($fails);
    }

    // --- V3 API bypass ---

    public function test_skips_validation_for_v3_api_request(): void
    {
        $request = \Illuminate\Http\Request::create(config('app.url').'/api/v3/test', 'GET');
        $this->app->instance('request', $request);

        $fails = false;
        $rule = new Honeypot();
        $rule->validate('honeypot', null, function () use (&$fails): void {
            $fails = true;
        });

        $this->assertFalse($fails);
    }

    // --- implicit flag ---

    public function test_rule_is_implicit(): void
    {
        $this->assertTrue($this->rule->implicit);
    }
}
