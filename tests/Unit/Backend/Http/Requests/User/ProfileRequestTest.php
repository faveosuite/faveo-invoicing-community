<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests\User;

use App\Http\Requests\User\AdminProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\ClientProfileRequest;
use App\Http\Requests\User\RegisterRequest;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * One Form Request per user form; they share ProfileRequest's authorisation and messages.
 */
class ProfileRequestTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue(new RegisterRequest()->authorize());
    }

    public function test_admin_profile_rules(): void
    {
        $this->actingAs(User::factory()->create());

        $rules = new AdminProfileRequest()->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('timezone_id', $rules);
    }

    public function test_client_profile_rules_skip_email_and_mobile(): void
    {
        $rules = new ClientProfileRequest()->rules();

        $this->assertArrayHasKey('first_name', $rules);
        $this->assertArrayNotHasKey('email', $rules); // changed via the OTP flow only
        $this->assertArrayNotHasKey('mobile', $rules);
        $this->assertArrayHasKey('country', $rules);
    }

    public function test_change_password_rules(): void
    {
        $rules = new ChangePasswordRequest()->rules();

        $this->assertArrayHasKey('old_password', $rules);
        $this->assertArrayHasKey('new_password', $rules);
        $this->assertArrayHasKey('confirm_password', $rules);
    }

    public function test_register_rules(): void
    {
        $rules = new RegisterRequest()->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
        $this->assertArrayHasKey('password_confirmation', $rules);
    }

    public function test_messages_are_shared_by_every_form(): void
    {
        foreach ([new RegisterRequest, new ClientProfileRequest, new ChangePasswordRequest] as $request) {
            $messages = $request->messages();
            $this->assertArrayHasKey('first_name.required', $messages);
            $this->assertArrayHasKey('new_password.required', $messages);
        }
    }
}
