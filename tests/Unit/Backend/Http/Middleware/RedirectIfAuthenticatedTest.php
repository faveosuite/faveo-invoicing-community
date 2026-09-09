<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\RedirectIfAuthenticated;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class RedirectIfAuthenticatedTest extends TestCase
{
    use DatabaseTransactions;

    private function next(): \Closure
    {
        return fn ($req) => response('passed');
    }

    public function test_passes_through_for_guest(): void
    {
        $request = Request::create('/login', 'GET');

        $response = (new RedirectIfAuthenticated())->handle($request, $this->next());

        $this->assertSame('passed', $response->getContent());
    }

    public function test_redirects_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $request = Request::create('/login', 'GET');
        $response = (new RedirectIfAuthenticated())->handle($request, $this->next());

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString('my-invoices', $response->headers->get('Location'));
    }
}
