<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Common;

use App\Http\Requests\Common\SettingRequest;
use Tests\TestCase;

class SettingRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new SettingRequest())->authorize());
    }

    public function test_rules_contains_required_keys(): void
    {
        $rules = (new SettingRequest())->rules();

        $this->assertArrayHasKey('company', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('driver', $rules);
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new SettingRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('company.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('password.required', $messages);
    }
}
