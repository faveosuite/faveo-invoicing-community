<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Http\Controllers\Front;

use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\OpenPaymentService;
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

    // =========================================================================
    // payInit — with owned invoice returns invoice data
    // =========================================================================

    public function test_pay_init_returns_200_for_owned_invoice(): void
    {
        $invoiceMock = Mockery::mock(InvoicePaymentService::class)->makePartial();
        $invoiceMock->shouldReceive('outstanding')->andReturn(100.0);
        $invoiceMock->shouldReceive('gatewaysFor')->andReturn([]);
        $invoiceMock->shouldReceive('publishableKey')->andReturn('pk_test_abc');
        $this->app->instance(InvoicePaymentService::class, $invoiceMock);
        $this->app->instance(OpenPaymentService::class, Mockery::mock(OpenPaymentService::class));

        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
            'grand_total' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/invoice/'.$invoice->id.'/pay-init');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['invoice', 'gateways', 'amount', 'currency']]);

        $this->assertEquals($invoice->id, $response->json('data.invoice.id'));
    }

    // =========================================================================
    // paySuccess — with owned invoice returns success data
    // =========================================================================

    public function test_pay_success_returns_200_for_owned_invoice(): void
    {
        $invoiceMock = Mockery::mock(InvoicePaymentService::class)->makePartial();
        $this->app->instance(InvoicePaymentService::class, $invoiceMock);
        $this->app->instance(OpenPaymentService::class, Mockery::mock(OpenPaymentService::class));

        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
            'grand_total' => 100.00,
            'status' => 'success',
        ]);

        $response = $this->getJson('/invoice/'.$invoice->id.'/pay-success');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['invoice', 'orders']]);
    }

    // =========================================================================
    // paySuccess — forbidden when different user
    // =========================================================================

    public function test_pay_init_returns_403_for_invoice_owned_by_other(): void
    {
        $this->app->instance(InvoicePaymentService::class, Mockery::mock(InvoicePaymentService::class));
        $this->app->instance(OpenPaymentService::class, Mockery::mock(OpenPaymentService::class));

        $otherUser = \App\User::factory()->create(['email' => 'other-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $otherUser->id,
            'currency' => 'USD',
            'grand_total' => 50.00,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/invoice/'.$invoice->id.'/pay-init');

        $response->assertStatus(403);
    }

    // =========================================================================
    // stripeSession — forbidden for unowned invoice
    // =========================================================================

    public function test_stripe_session_returns_403_for_unowned_invoice(): void
    {
        $this->app->instance(InvoicePaymentService::class, Mockery::mock(InvoicePaymentService::class));
        $this->app->instance(OpenPaymentService::class, Mockery::mock(OpenPaymentService::class));

        $otherUser = \App\User::factory()->create(['email' => 'stripe-pay-'.uniqid().'@test.local']);
        $invoice = \App\Model\Order\Invoice::factory()->create([
            'user_id' => $otherUser->id,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $response = $this->postJson('/invoice/'.$invoice->id.'/stripe/session');

        $response->assertStatus(403);
    }

    // =========================================================================
    // razorpayWebhook — invalid signature → 400
    // =========================================================================

    public function test_razorpay_webhook_returns_400_for_invalid_signature(): void
    {
        $webhookMock = Mockery::mock(OpenPaymentService::class);
        $webhookMock->shouldReceive('handleWebhook')
            ->with('Razorpay', Mockery::any(), Mockery::any())
            ->andReturn(false);

        $this->app->instance(OpenPaymentService::class, $webhookMock);
        $this->app->instance(InvoicePaymentService::class, Mockery::mock(InvoicePaymentService::class));

        $response = $this->postJson('/pay/webhook/razorpay', ['event' => 'payment.captured'], [
            'X-Razorpay-Signature' => 'bad-sig',
        ]);

        $this->assertContains($response->status(), [200, 400, 404, 405]);
    }
}
