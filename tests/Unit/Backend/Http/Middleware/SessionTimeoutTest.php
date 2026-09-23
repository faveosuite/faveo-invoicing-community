<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\SessionTimeout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_resets_timer_and_passes_when_just_started(): void
    {
        Session::put('justStarted', true);
        $request = Request::create('/verify', 'GET');

        $response = (new SessionTimeout())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
        $this->assertTrue(Session::has('lastVerificationActivity'));
    }

    public function test_initialises_timer_when_session_key_missing(): void
    {
        Session::forget('lastVerificationActivity');
        $request = Request::create('/verify', 'GET');

        $response = (new SessionTimeout())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
        $this->assertTrue(Session::has('lastVerificationActivity'));
    }

    public function test_passes_when_session_not_expired(): void
    {
        Session::put('lastVerificationActivity', Date::now()->subMinutes(5)->timestamp);
        $request = Request::create('/verify', 'GET');

        $response = (new SessionTimeout())->handle($request, $this->next(), 10);

        $this->assertSame('passed', $response->getContent());
    }

    public function test_redirects_to_login_when_session_expired(): void
    {
        Session::put('lastVerificationActivity', Date::now()->subMinutes(15)->timestamp);
        $request = Request::create('/verify', 'GET');

        $response = (new SessionTimeout())->handle($request, $this->next(), 10);

        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_returns_json_error_when_session_expired_and_json_request(): void
    {
        Session::put('lastVerificationActivity', Date::now()->subMinutes(15)->timestamp);
        $request = Request::create('/verify', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = (new SessionTimeout())->handle($request, $this->next(), 10);

        $this->assertSame(401, $response->getStatusCode());
    }
}
