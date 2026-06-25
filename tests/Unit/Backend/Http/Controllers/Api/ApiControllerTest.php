<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_csp_report_endpoint_returns_200(): void
    {
        Log::shouldReceive('channel')->with('csp')->andReturnSelf();
        Log::shouldReceive('info')->once();

        $response = $this->postJson('/api/csp-report', [
            'csp-report' => [
                'document-uri' => 'https://example.com',
                'violated-directive' => 'script-src',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_csp_report_with_empty_body_returns_200(): void
    {
        Log::shouldReceive('channel')->with('csp')->andReturnSelf();
        Log::shouldReceive('info')->once();

        $response = $this->post('/api/csp-report', [], ['Content-Type' => 'application/csp-report']);
        $response->assertStatus(200);
    }
}
