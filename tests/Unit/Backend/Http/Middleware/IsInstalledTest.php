<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\IsInstalled;
use Illuminate\Http\Request;
use Tests\TestCase;

class IsInstalledTest extends TestCase
{
    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_passes_through_when_not_installed(): void
    {
        config(['custom.db_install' => 0]);
        $request = Request::create('/install', 'GET');

        $response = (new IsInstalled())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }

    public function test_returns_json_when_installed_and_json_request(): void
    {
        config(['custom.db_install' => 1]);
        $request = Request::create('/install', 'POST');
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');

        $response = (new IsInstalled())->handle($request, $this->next());

        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('already installed', $data['result']['fails']);
    }

    public function test_redirects_when_installed_and_html_request(): void
    {
        config(['custom.db_install' => 1]);
        $request = Request::create('/install', 'GET');

        $response = (new IsInstalled())->handle($request, $this->next());

        $this->assertSame(302, $response->getStatusCode());
    }
}
