<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\PreferredDomain;
use Illuminate\Http\Request;
use Tests\TestCase;

class PreferredDomainTest extends TestCase
{
    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_redirects_www_to_non_www(): void
    {
        $request = Request::create('http://www.example.com/page', 'GET');
        $request->headers->set('host', 'www.example.com');

        $response = (new PreferredDomain())->handle($request, $this->next());

        $this->assertSame(301, $response->getStatusCode());
        $this->assertStringContainsString('example.com', $response->headers->get('Location'));
        $this->assertStringNotContainsString('www.', $response->headers->get('Location'));
    }

    public function test_passes_through_non_www_request(): void
    {
        $request = Request::create('http://example.com/page', 'GET');
        $request->headers->set('host', 'example.com');

        $response = (new PreferredDomain())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }
}
