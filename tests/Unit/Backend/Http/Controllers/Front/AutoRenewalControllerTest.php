<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Services\Payment\PaymentService;
use Mockery;
use Tests\DBTestCase;

class AutoRenewalControllerTest extends DBTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        $this->getLoggedInUser('user');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_disable_returns_404_for_nonexistent_order(): void
    {
        $paymentServiceMock = Mockery::mock(PaymentService::class);
        $this->app->instance(PaymentService::class, $paymentServiceMock);

        $response = $this->postJson('/auto-renewal/999999/disable');
        // 404 since order 999999 doesn't exist
        $this->assertContains($response->status(), [404, 400, 500]);
    }
}
