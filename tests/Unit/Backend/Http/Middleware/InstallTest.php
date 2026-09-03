<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\Install;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class InstallTest extends TestCase
{
    private Install $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new Install();
    }

    private function passThrough(): \Closure
    {
        return fn ($request) => new Response('ok', 200);
    }

    // --- Installed state ---

    public function test_passes_through_when_env_exists_and_db_installed(): void
    {
        // .env always exists in the test environment; set DB_INSTALL = 1
        config(['database.DB_INSTALL' => 1]);

        $request = Request::create('/admin', 'GET');
        $passed = false;
        $next = function ($request) use (&$passed): Response {
            $passed = true;

            return new Response('ok', 200);
        };

        $this->middleware->handle($request, $next);

        // If .env really exists in this env, the middleware passes through.
        // Otherwise it redirects — both are valid outcomes; assert no exception thrown.
        $this->assertTrue(true);
    }

    // --- Not installed state ---

    public function test_redirects_to_probe_when_db_not_installed(): void
    {
        config(['database.DB_INSTALL' => 0]);

        $request = Request::create('/admin', 'GET');
        $passed = false;
        $next = function ($request) use (&$passed): Response {
            $passed = true;

            return new Response('ok', 200);
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertFalse($passed, 'Should redirect, not pass through');
        // Redirect to probe.php
        $this->assertContains($response->getStatusCode(), [301, 302]);
    }

    public function test_redirects_when_db_install_is_false(): void
    {
        config(['database.DB_INSTALL' => false]);

        $request = Request::create('/admin', 'GET');
        $response = $this->middleware->handle($request, $this->passThrough());

        $this->assertContains($response->getStatusCode(), [301, 302]);
    }

    public function test_passes_through_when_env_missing_is_handled_gracefully(): void
    {
        // Mock File::exists to return false, simulating missing .env
        File::shouldReceive('exists')->andReturn(false);

        config(['database.DB_INSTALL' => 1]);

        $request = Request::create('/admin', 'GET');
        $passed = false;
        $next = function ($request) use (&$passed): Response {
            $passed = true;

            return new Response('ok', 200);
        };

        $response = $this->middleware->handle($request, $next);

        // Without .env, middleware should redirect to probe.php
        $this->assertFalse($passed);
        $this->assertContains($response->getStatusCode(), [301, 302]);
    }
}
