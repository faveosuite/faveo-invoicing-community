<?php

namespace Tests\Unit\Backend\Http\Controllers\Auth;

use Tests\DBTestCase;

class SecurityTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\Install::class);
    }

    // SPA catch-all routes — served as 200 regardless of auth (Vue handles auth client-side)
    protected array $spaUrls = [
        'show/cart',
        'group/1/1',
    ];

    // Web routes that require authentication — guests get redirected
    protected array $protectedWebUrls = [
        'my-cart/checkout',
        'settings/email',
    ];

    public function test_login_page_returns_200_for_guests(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_spa_urls_return_200_via_fallback_route(): void
    {
        foreach ($this->spaUrls as $url) {
            $response = $this->get($url);
            $response->assertStatus(200, "SPA fallback should serve 200 for {$url}");
        }
    }

    public function test_protected_web_urls_redirect_guests(): void
    {
        foreach ($this->protectedWebUrls as $url) {
            $response = $this->get($url);
            $response->assertRedirect();
        }
    }

    public function test_login_route_returns_200_for_authenticated_users_spa_handles_redirect(): void
    {
        $this->getLoggedInUser();
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
}
