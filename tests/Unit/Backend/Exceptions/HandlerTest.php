<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Exceptions;

use App\Exceptions\Handler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use RuntimeException;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    private Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->app->make(Handler::class);
    }

    public function test_report_calls_logger_when_installed(): void
    {
        config(['custom.db_install' => 1]);

        \Logger::shouldReceive('exception')->once();

        $this->handler->report(new RuntimeException('install test'));
    }

    public function test_unauthenticated_returns_json_for_json_request(): void
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $this->handler->render($request, new AuthenticationException());

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_unauthenticated_redirects_for_html_request(): void
    {
        $request = Request::create('/test', 'GET');

        $response = $this->handler->render($request, new AuthenticationException());

        $this->assertEquals(302, $response->getStatusCode());
    }
}
