<?php

namespace Tests\Unit\Backend\Services\Payment;

use App\Services\Payment\WebhookDispatcher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\DBTestCase;

class SubscriptionWebhookServiceTest extends DBTestCase
{
    use DatabaseTransactions;

    // =========================================================================
    // WebhookDispatcher — on() and dispatch()
    // =========================================================================

    public function test_dispatcher_on_registers_handler(): void
    {
        $called = false;
        $dispatcher = (new WebhookDispatcher)->on('test.event', function () use (&$called): void {
            $called = true;
        });
        $dispatcher->dispatch('test.event', []);
        $this->assertTrue($called);
    }

    public function test_dispatcher_dispatch_with_no_handler_does_not_throw(): void
    {
        $dispatcher = new WebhookDispatcher;
        // No handler registered — should silently do nothing
        $dispatcher->dispatch('unknown.event', []);
        $this->assertTrue(true);
    }

    public function test_dispatcher_on_accepts_array_of_events(): void
    {
        $count = 0;
        $dispatcher = (new WebhookDispatcher)->on(
            ['event.a', 'event.b'],
            function () use (&$count): void {
                $count++;
            }
        );
        $dispatcher->dispatch('event.a', []);
        $dispatcher->dispatch('event.b', []);
        $this->assertEquals(2, $count);
    }

    public function test_dispatcher_on_returns_self_for_chaining(): void
    {
        $dispatcher = new WebhookDispatcher;
        $result = $dispatcher->on('e', fn () => null);
        $this->assertSame($dispatcher, $result);
    }

    public function test_dispatcher_stripe_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    public function test_dispatcher_razorpay_factory_returns_dispatcher_instance(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $this->assertInstanceOf(WebhookDispatcher::class, $dispatcher);
    }

    // =========================================================================
    // WebhookDispatcher — Stripe payment_intent.payment_failed (no DB row → silent)
    // =========================================================================

    public function test_stripe_dispatcher_handles_payment_intent_failed_for_unknown_order(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        // Dispatching with unknown order_id → finds no row → silent
        $dispatcher->dispatch('payment_intent.payment_failed', [
            'data' => [
                'object' => [
                    'metadata' => ['order_id' => 999999],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_stripe_dispatcher_handles_checkout_session_for_unknown_invoice(): void
    {
        $dispatcher = WebhookDispatcher::stripe();
        $dispatcher->dispatch('checkout.session.completed', [
            'data' => [
                'object' => [
                    'metadata' => ['invoice_id' => 999999],
                    'payment_intent' => 'pi_test123',
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_razorpay_dispatcher_handles_payment_captured_for_unknown_invoice(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.captured', [
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'notes' => ['invoice_id' => 999999],
                        'id' => 'pay_test123',
                    ],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }

    public function test_razorpay_dispatcher_handles_payment_failed_for_unknown_order(): void
    {
        $dispatcher = WebhookDispatcher::razorpay();
        $dispatcher->dispatch('payment.failed', [
            'event' => 'payment.failed',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'notes' => ['order_id' => 999999],
                        'id' => 'pay_fail123',
                    ],
                ],
            ],
        ]);
        $this->assertTrue(true);
    }
}
