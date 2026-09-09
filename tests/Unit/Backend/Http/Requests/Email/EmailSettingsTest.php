<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Email;

use App\Http\Requests\Email\EmailSettingRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class EmailSettingsTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_example(): void
    {
        $this->assertTrue(condition: true);
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new EmailSettingRequest())->authorize());
    }

    public function test_smtp_driver_rules_require_port_and_host(): void
    {
        $request = new EmailSettingRequest();
        $request->merge(['driver' => 'smtp']);
        $rules = $request->rules();

        $validator = Validator::make(['driver' => 'smtp', 'password' => 'secret'], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('port', $validator->errors()->toArray());
    }

    public function test_non_smtp_driver_requires_driver_and_email(): void
    {
        $request = new EmailSettingRequest;
        $request->merge(['driver' => 'sendmail']);
        $rules = $request->rules();

        $validator = Validator::make(['driver' => 'sendmail'], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_domain_mismatch_fails_closure_validation(): void
    {
        $request = new EmailSettingRequest;
        $rules = $request->rules();

        // Use an email domain that won't match the app URL host
        $validator = Validator::make(
            ['driver' => 'sendmail', 'email' => 'test@different-domain-xyz.com'],
            $rules
        );

        $this->assertTrue($validator->fails());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new EmailSettingRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('driver.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
    }
}
