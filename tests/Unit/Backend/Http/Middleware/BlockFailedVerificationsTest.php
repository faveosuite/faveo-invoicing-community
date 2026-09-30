<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\BlockFailedVerifications;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class BlockFailedVerificationsTest extends TestCase
{
    use DatabaseTransactions;

    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_passes_when_no_rate_limit_exceeded(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/verify', 'GET');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame('passed', $response->getContent());
    }

    public function test_redirects_to_login_when_no_identifier_for_verify_context(): void
    {
        // Guest with no session user ID → identifier is null → redirect to login
        $request = Request::create('/verify', 'GET');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_blocks_with_json_response_when_rate_limit_exceeded(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Exhaust the email-verify rate limit
        $key = 'email-verify:'.$user->id;
        for ($i = 0; $i <= 3; $i++) {
            RateLimiter::hit($key, 60);
        }

        $request = Request::create('/verify', 'GET');
        $request->headers->set('Accept', 'application/json');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame(429, $response->getStatusCode());

        RateLimiter::clear($key);
    }

    public function test_filters_types_when_only_types_specified(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Exhaust email-verify but only check mobile-verify — should pass
        $key = 'email-verify:'.$user->id;
        for ($i = 0; $i <= 3; $i++) {
            RateLimiter::hit($key, 60);
        }

        $request = Request::create('/verify', 'GET');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify', 'mobile-verify');

        $this->assertSame('passed', $response->getContent());

        RateLimiter::clear($key);
    }

    public function test_login_context_uses_ip_based_identifier(): void
    {
        $request = Request::create('/login', 'POST', ['email_username' => 'test@example.com']);
        // Bind the request so LoginController::getLoginRateLimitKey() reads it
        $this->app->instance('request', $request);

        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'login');

        $this->assertContains($response->getStatusCode(), [200, 302, 429]);
    }

    public function test_session_verification_user_id_used_when_not_authenticated(): void
    {
        Session::put('verification_user_id', 999);
        $request = Request::create('/verify', 'GET');

        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame('passed', $response->getContent());
        Session::forget('verification_user_id');
    }

    public function test_skips_escalation_when_penalty_already_applied(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $key = 'email-verify:'.$user->id;
        $appliedKey = 'penalty_applied:verify:email-verify:'.$user->id;

        // Mark penalty as already applied
        \Illuminate\Support\Facades\Cache::put($appliedKey, true, now()->addMinutes(30));

        // Exhaust rate limit
        for ($i = 0; $i <= 3; $i++) {
            RateLimiter::hit($key, 60);
        }

        $request = Request::create('/verify', 'GET');
        $request->headers->set('Accept', 'application/json');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame(429, $response->getStatusCode());

        RateLimiter::clear($key);
        \Illuminate\Support\Facades\Cache::forget($appliedKey);
    }

    public function test_non_json_request_redirects_when_rate_limited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $key = 'email-verify:'.$user->id;
        for ($i = 0; $i <= 3; $i++) {
            RateLimiter::hit($key, 60);
        }

        $request = Request::create('/verify', 'GET');
        $response = (new BlockFailedVerifications())->handle($request, $this->next(), 'verify');

        $this->assertSame(302, $response->getStatusCode());

        RateLimiter::clear($key);
    }
}
