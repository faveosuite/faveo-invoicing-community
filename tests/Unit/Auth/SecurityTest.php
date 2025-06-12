<?php

namespace Tests\Unit\Auth;

use Illuminate\Support\Facades\Config;
use Tests\DBTestCase;

class SecurityTest extends DBTestCase
{
    /**
     * List of URLs to test.
     */
    protected array $urls = [
        '/login',
        'show/cart',
        'password/reset',
        'group/1/1',
    ];

    /**
     * Test that each URL returns expected security headers.
     */
    public function test_security_headers_exist_on_each_url(): void
    {
//        config()->set('database.DB_INSTALL', true);

        foreach ($this->urls as $url) {
            $response = $this->get($url);

            // --- CSP Headers ---
            $response->assertHeader('Content-Security-Policy');
            $csp = $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString('default-src', $csp);
            $this->assertStringContainsString('script-src', $csp);
            $this->assertStringContainsString('style-src', $csp);
            $this->assertStringContainsString('cdn.datatables.net', $csp);

//            // --- X-Content-Type-Options ---
//            $response->assertHeader('X-Content-Type-Options', 'nosniff');
//
//            // --- X-Frame-Options ---
//            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        }
    }
}
