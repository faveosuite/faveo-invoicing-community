<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\User;

use App\Http\Requests\User\ProfileRequest;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProfileRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new ProfileRequest())->authorize());
    }

    public function test_profile_segment_rules_require_first_name(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = ProfileRequest::create('/profile', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('timezone_id', $rules);
    }

    public function test_my_profile_segment_rules(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = ProfileRequest::create('/my-profile', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('country', $rules);
    }

    public function test_password_segment_rules(): void
    {
        $request = ProfileRequest::create('/password', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('old_password', $rules);
        $this->assertArrayHasKey('new_password', $rules);
        $this->assertArrayHasKey('confirm_password', $rules);
    }

    public function test_my_password_segment_rules(): void
    {
        $request = ProfileRequest::create('/my-password', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('old_password', $rules);
        $this->assertArrayHasKey('new_password', $rules);
    }

    public function test_auth_segment_rules(): void
    {
        $request = ProfileRequest::create('/auth', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('password_confirmation', $rules);
    }

    public function test_other_segment_returns_empty_rules(): void
    {
        $request = ProfileRequest::create('/unknown', 'POST');
        $request = ProfileRequest::createFrom($request, new ProfileRequest());

        $this->assertSame([], $request->rules());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new ProfileRequest())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('first_name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('old_password.required', $messages);
        $this->assertArrayHasKey('new_password.required', $messages);
    }
}
