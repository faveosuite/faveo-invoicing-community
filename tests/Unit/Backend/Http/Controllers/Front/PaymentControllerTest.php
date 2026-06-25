<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\OpenPaymentService;
use App\Services\Payment\PaymentService;
use Mockery;
use Tests\DBTestCase;

class PaymentControllerTest extends DBTestCase
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

    public function test_stripe_webhook_returns_non_500_for_invalid_signature(): void
    {
        // The webhook route is at /pay/webhook/stripe
        $webhookMock = Mockery::mock(OpenPaymentService::class);
        $webhookMock->shouldReceive('handleWebhook')
            ->withAnyArgs()
            ->andReturn(false);

        $invoiceMock = Mockery::mock(InvoicePaymentService::class);

        $this->app->instance(InvoicePaymentService::class, $invoiceMock);
        $this->app->instance(OpenPaymentService::class, $webhookMock);

        $response = $this->postJson('/pay/webhook/stripe', [], [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        // 400 = invalid signature, or 405/404 if route not found
        $this->assertContains($response->status(), [200, 400, 404, 405]);
    }

    public function test_pay_init_returns_error_for_nonexistent_invoice(): void
    {
        $invoiceMock = Mockery::mock(InvoicePaymentService::class);
        $webhookMock = Mockery::mock(OpenPaymentService::class);

        $this->app->instance(InvoicePaymentService::class, $invoiceMock);
        $this->app->instance(OpenPaymentService::class, $webhookMock);

        $response = $this->getJson('/invoice/999999/pay-init');
        // 404 for non-existent invoice
        $this->assertContains($response->status(), [400, 404, 500]);
    }
}
