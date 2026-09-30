<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Requests;

use App\Http\Requests\verifyOtp;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class verifyOtpTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue((new verifyOtp())->authorize());
    }

    private function makeRequest(string $email): verifyOtp
    {
        // Must be POST so $this->request->get() reads from the POST bag
        $base = \Illuminate\Http\Request::create('/verify-otp', 'POST', ['newemail' => $email]);

        return verifyOtp::createFrom($base, new verifyOtp());
    }

    public function test_rules_contains_expected_keys(): void
    {
        $rules = $this->makeRequest('nonexistent@example.com')->rules();

        $this->assertArrayHasKey('verify_email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_password_closure_fails_when_hash_does_not_match(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $rules = $this->makeRequest($user->email)->rules();

        $validator = Validator::make(['password' => 'wrong-password'], $rules);
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_password_closure_passes_when_hash_matches(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $rules = $this->makeRequest($user->email)->rules();

        $validator = Validator::make(['password' => 'correct-password'], $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_messages_returns_expected_keys(): void
    {
        $messages = (new verifyOtp())->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('verify_email.required', $messages);
        $this->assertArrayHasKey('password.required', $messages);
    }
}
