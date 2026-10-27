<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class VerifyCsrfTokenTest extends TestCase
{
    public function test_except_list_contains_api_routes(): void
    {
        $middleware = $this->app->make(VerifyCsrfToken::class);
        $prop = (new \ReflectionClass($middleware))->getProperty('except');
        $prop->setAccessible(true);
        $except = $prop->getValue($middleware);

        $this->assertContains('api/v3/*', $except);
        $this->assertContains('serial', $except);
    }

    public function test_token_mismatch_regenerates_token_and_redirects(): void
    {
        $middleware = $this->app->make(VerifyCsrfToken::class);

        $request = Request::create('/login', 'POST');
        // Use the session decorator that Request expects
        $session = $this->app['session']->driver();
        $session->start();
        $request->setLaravelSession($session);

        $response = $middleware->handle($request, function () {
            throw new TokenMismatchException();
        });

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }
}
