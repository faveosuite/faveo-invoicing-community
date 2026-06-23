<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\SecurityEnforcer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityEnforcerTest extends TestCase
{
    private SecurityEnforcer $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityEnforcer();
    }

    private function passThrough(): \Closure
    {
        return fn ($request) => new Response('ok', 200);
    }

    // --- DB_INSTALL = false (not installed) ---

    public function test_passes_through_when_db_not_installed(): void
    {
        config(['database.DB_INSTALL' => false]);

        $request = Request::create('/test', 'GET');
        $passed = false;
        $next = function ($request) use (&$passed): Response {
            $passed = true;

            return new Response('ok', 200);
        };

        $this->middleware->handle($request, $next);

        $this->assertTrue($passed);
    }

    // --- DB_INSTALL = true (installed) ---

    public function test_adds_x_frame_options_header_when_installed(): void
    {
        config(['database.DB_INSTALL' => 1]);

        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, $this->passThrough());

        $this->assertSame('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    public function test_adds_x_content_type_options_header_when_installed(): void
    {
        config(['database.DB_INSTALL' => 1]);

        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, $this->passThrough());

        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_security_headers_not_added_when_not_installed(): void
    {
        config(['database.DB_INSTALL' => false]);

        $request = Request::create('/test', 'GET');
        $response = $this->middleware->handle($request, $this->passThrough());

        $this->assertNull($response->headers->get('X-Frame-Options'));
    }

    public function test_returns_200_response_when_installed(): void
    {
        config(['database.DB_INSTALL' => 1]);
        config(['app.url' => 'http://example.com']); // http so no redirect

        $request = Request::create('http://example.com/test', 'GET');
        $response = $this->middleware->handle($request, $this->passThrough());

        $this->assertSame(200, $response->getStatusCode());
    }

    
}
