<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Order\Invoice;
use App\Model\Payment\OpenPaymentOrder;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\WebhookDispatcher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\TestCase;

class WebhookDispatcherTest extends TestCase
{
    use DatabaseTransactions;

    public function test_on_registers_single_event_handler(): void
    {
        $dispatcher = new WebhookDispatcher();
        $called = false;

        $dispatcher->on('payment.success', function () use (&$called): void {
            $called = true;
        });

        $dispatcher->dispatch('payment.success', []);

        $this->assertTrue($called);
    }

    public function test_on_registers_multiple_events(): void
    {
        $dispatcher = new WebhookDispatcher();
        $calls = [];

        $dispatcher->on(['event.a', 'event.b'], function (array $e) use (&$calls): void {
            $calls[] = $e['type'];
        });

        $dispatcher->dispatch('event.a', ['type' => 'a']);
        $dispatcher->dispatch('event.b', ['type' => 'b']);

        $this->assertSame(['a', 'b'], $calls);
    }

    public function test_dispatch_does_nothing_for_unregistered_event(): void
    {
        $dispatcher = new WebhookDispatcher();
        $dispatcher->dispatch('unknown.event', []);
        $this->assertTrue(true); // no exception
    }

    public function test_on_returns_fluent_self(): void
    {
        $dispatcher = new WebhookDispatcher();
        $result = $dispatcher->on('event', fn () => null);
        $this->assertSame($dispatcher, $result);
    }

    public function test_stripe_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    public function test_razorpay_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    public function test_stripe_handles_unknown_event_without_error(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('unknown.stripe.event', ['data' => []]);
        $this->assertTrue(true);
    }

    public function test_razorpay_handles_unknown_event_without_error(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('unknown.razorpay.event', []);
        $this->assertTrue(true);
    }

    public function test_stripe_checkout_session_completed_with_no_metadata(): void
    {
        // confirmStripePayment with empty object → no invoice_id, no order_id → no DB call
        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', ['data' => ['object' => []]]);
        $this->assertTrue(true);
    }

    public function test_stripe_payment_intent_failed_with_no_metadata(): void
    {
        // failStripePayment with empty object → no order_id → no DB call
        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('payment_intent.payment_failed', ['data' => ['object' => []]]);
        $this->assertTrue(true);
    }

    public function test_razorpay_payment_captured_with_no_metadata(): void
    {
        // handleRazorpayPayment with empty payload → no invoice_id, no order_id → no DB call
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.captured', ['event' => 'payment.captured', 'payload' => ['payment' => ['entity' => []]]]);
        $this->assertTrue(true);
    }

    public function test_razorpay_payment_failed_with_no_metadata(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.failed', ['event' => 'payment.failed', 'payload' => ['payment' => ['entity' => []]]]);
        $this->assertTrue(true);
    }

    public function test_stripe_checkout_session_confirms_invoice_when_found(): void
    {
        $invoice = Invoice::factory()->create();

        $mockService = Mockery::mock(InvoicePaymentService::class);
        $mockService->shouldReceive('confirm')->once();
        $this->app->instance(InvoicePaymentService::class, $mockService);

        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', [
            'data' => ['object' => ['metadata' => ['invoice_id' => $invoice->id], 'id' => 'pi_test']],
        ]);

        $this->assertTrue(true);
    }

    public function test_stripe_checkout_session_with_nonexistent_invoice_skips_confirm(): void
    {
        $mockService = Mockery::mock(InvoicePaymentService::class);
        $mockService->shouldNotReceive('confirm');
        $this->app->instance(InvoicePaymentService::class, $mockService);

        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', [
            'data' => ['object' => ['metadata' => ['invoice_id' => 999999999]]],
        ]);

        $this->assertTrue(true);
    }

    public function test_stripe_payment_failed_with_open_order(): void
    {
        $order = OpenPaymentOrder::create([
            'payment_status' => 'pending',
            'gateway' => 'Stripe',
            'amount' => 100,
            'currency' => 'USD',
            'reference' => 'test-ref-'.uniqid(),
        ]);

        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('payment_intent.payment_failed', [
            'data' => ['object' => ['metadata' => ['order_id' => $order->id]]],
        ]);

        $this->assertSame('failed', $order->fresh()->payment_status);
    }

    public function test_stripe_checkout_completes_open_order_when_found(): void
    {
        $order = OpenPaymentOrder::create([
            'payment_status' => 'pending',
            'gateway' => 'Stripe',
            'amount' => 100,
            'currency' => 'USD',
            'reference' => 'test-ref-'.uniqid(),
        ]);

        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', [
            'data' => ['object' => ['metadata' => ['order_id' => $order->id], 'id' => 'pi_test']],
        ]);

        $this->assertSame('completed', $order->fresh()->payment_status);
    }

    public function test_razorpay_payment_captured_confirms_invoice_when_found(): void
    {
        $invoice = Invoice::factory()->create();

        $mockService = Mockery::mock(InvoicePaymentService::class);
        $mockService->shouldReceive('confirm')->once();
        $this->app->instance(InvoicePaymentService::class, $mockService);

        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.captured', [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['notes' => ['invoice_id' => $invoice->id], 'id' => 'pay_test']]],
        ]);

        $this->assertTrue(true);
    }

    public function test_razorpay_payment_captured_completes_open_order(): void
    {
        $order = OpenPaymentOrder::create([
            'payment_status' => 'pending',
            'gateway' => 'Razorpay',
            'amount' => 100,
            'currency' => 'USD',
            'reference' => 'test-ref-'.uniqid(),
        ]);

        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.captured', [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['notes' => ['order_id' => $order->id], 'id' => 'pay_test']]],
        ]);

        $this->assertSame('completed', $order->fresh()->payment_status);
    }

    public function test_razorpay_payment_failed_marks_open_order_failed(): void
    {
        $order = OpenPaymentOrder::create([
            'payment_status' => 'pending',
            'gateway' => 'Razorpay',
            'amount' => 100,
            'currency' => 'USD',
            'reference' => 'test-ref-'.uniqid(),
        ]);

        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.failed', [
            'event' => 'payment.failed',
            'payload' => ['payment' => ['entity' => ['notes' => ['order_id' => $order->id]]]],
        ]);

        $this->assertSame('failed', $order->fresh()->payment_status);
    }
}
