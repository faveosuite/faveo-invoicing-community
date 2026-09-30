<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\Authenticate;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthenticateTest extends TestCase
{
    use DatabaseTransactions;

    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_guest_ajax_request_returns_401(): void
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');

        $middleware = new Authenticate($this->app['auth']->guard());
        $response = $middleware->handle($request, $this->next());

        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_guest_non_ajax_redirects_to_login(): void
    {
        $request = Request::create('/dashboard', 'GET');

        $middleware = new Authenticate($this->app['auth']->guard());
        $response = $middleware->handle($request, $this->next());

        $this->assertSame(302, $response->getStatusCode());
    }

    public function test_active_user_passes_through(): void
    {
        $user = User::factory()->create(['active' => 1]);
        $this->actingAs($user);

        $request = Request::create('/dashboard', 'GET');
        $middleware = new Authenticate($this->app['auth']->guard());
        $response = $middleware->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }

    public function test_inactive_user_is_logged_out_and_redirected(): void
    {
        $user = User::factory()->create(['active' => 0]);
        $this->actingAs($user);

        $request = Request::create('/dashboard', 'GET');
        $middleware = new Authenticate($this->app['auth']->guard());
        $response = $middleware->handle($request, $this->next());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertFalse($this->app['auth']->check());
    }
}
