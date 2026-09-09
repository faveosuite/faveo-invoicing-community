<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Middleware;

use App\Http\Middleware\AddJsonAcceptHeader;
use Illuminate\Http\Request;
use Tests\TestCase;

class AddJsonAcceptHeaderTest extends TestCase
{
    private function next(): \Closure
    {
        return fn ($req) => response('ok');
    }

    public function test_sets_json_accept_header_for_normal_request(): void
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new AddJsonAcceptHeader();

        $middleware->handle($request, $this->next());

        $this->assertSame('application/json', $request->header('Accept'));
    }

    public function test_passes_request_through(): void
    {
        $request = Request::create('/api/test', 'GET');
        $middleware = new AddJsonAcceptHeader();

        $response = $middleware->handle($request, $this->next());

        $this->assertSame('ok', $response->getContent());
    }
}
