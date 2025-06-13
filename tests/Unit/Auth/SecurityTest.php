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

        foreach ($this->urls as $url) {
            $response = $this->get($url);

            // --- CSP Headers ---
            $response->assertHeader('Content-Security-Policy');
            $csp = $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString("default-src", $csp);
            $this->assertStringContainsString("script-src", $csp);
            $this->assertStringContainsString("style-src", $csp);
            $this->assertStringContainsString("cdn.datatables.net", $csp);
        }
    }

    /**
     * This test deliberately fails to verify negative testing behavior.
     */
    public function test_security_headers_fail_on_missing_csp(): void
    {

        foreach ($this->urls as $url) {
            $response = $this->get($url);

            // --- CSP Headers ---
            // Valid assertion - this should pass
            $this->assertStringContainsString('default-src', $response->headers->get('Content-Security-Policy'));

            // These assertions are meant to fail deliberately
            $response->assertHeader('Content-Security-Policy');
            $csp = $response->headers->get('Content-Security-Policy');

            // Failing assertions
            $this->assertStringNotContainsString("cdn.unknown.com", $csp);
            $this->assertStringNotContainsString("malicious-site.com", $csp);
        }
    }

}
