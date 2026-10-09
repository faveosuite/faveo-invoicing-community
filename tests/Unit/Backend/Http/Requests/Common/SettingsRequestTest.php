<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\Common;

use App\Http\Requests\Common\SettingsRequest;
use Tests\TestCase;

class SettingsRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new SettingsRequest())->authorize());
    }

    public function test_rules_contains_required_keys(): void
    {
        $rules = (new SettingsRequest())->rules();

        $this->assertArrayHasKey('company', $rules);
        $this->assertArrayHasKey('website', $rules);
        $this->assertArrayHasKey('default_currency', $rules);
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new SettingsRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('company.required', $messages);
        $this->assertArrayHasKey('website.required', $messages);
        $this->assertArrayHasKey('default_currency.required', $messages);
    }
}
