<?php

declare(strict_types=1);

namespace Tests\Unit\Backend\Services\Payment;

use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use App\Model\Order\Invoice;
use App\Model\Order\Payment;
use App\Services\Payment\PostPaymentService;
use App\User;
use Mockery;
use Tests\DBTestCase;

class PostPaymentServiceTest extends DBTestCase
{
    private PostPaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PostPaymentService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- recordPayment() (private) ---

    public function test_record_payment_creates_payment_for_outstanding_balance(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'grand_total' => 100.00,
            'status'      => 'pending',
        ]);

        $paymentsBefore = Payment::where('invoice_id', $invoice->id)->count();

        $this->getPrivateMethod($this->service, 'recordPayment', [$invoice, 'Stripe']);

        $paymentsAfter = Payment::where('invoice_id', $invoice->id)->count();
        $this->assertGreaterThan($paymentsBefore, $paymentsAfter);

        $payment = Payment::where('invoice_id', $invoice->id)->latest()->first();
        $this->assertNotNull($payment);
        $this->assertSame('success', $payment->payment_status);
        $this->assertSame('Stripe', $payment->payment_method);
    }

    public function test_record_payment_updates_invoice_status_to_success(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'grand_total' => 50.00,
            'status'      => 'pending',
        ]);

        $this->getPrivateMethod($this->service, 'recordPayment', [$invoice, 'Razorpay']);

        $invoice->refresh();
        $this->assertSame('success', strtolower((string) $invoice->status));
    }

    public function test_record_payment_is_idempotent_when_already_fully_paid(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'grand_total' => 100.00,
            'status'      => 'success',
        ]);

        // Pre-create a successful payment covering the full amount.
        Payment::create([
            'invoice_id'     => $invoice->id,
            'user_id'        => $invoice->user_id,
            'amount'         => 100.00,
            'payment_method' => 'Stripe',
            'payment_status' => 'success',
        ]);

        $countBefore = Payment::where('invoice_id', $invoice->id)->count();

        $this->getPrivateMethod($this->service, 'recordPayment', [$invoice, 'Stripe']);

        // Outstanding = grand_total - paid = 0 → no new record created.
        $countAfter = Payment::where('invoice_id', $invoice->id)->count();
        $this->assertSame($countBefore, $countAfter);
    }

    public function test_record_payment_only_covers_outstanding_amount_on_partial_payment(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create([
            'grand_total' => 100.00,
            'status'      => 'pending',
        ]);

        // A prior payment of 60.00 already recorded.
        Payment::create([
            'invoice_id'     => $invoice->id,
            'user_id'        => $invoice->user_id,
            'amount'         => 60.00,
            'payment_method' => 'Stripe',
            'payment_status' => 'success',
        ]);

        $this->getPrivateMethod($this->service, 'recordPayment', [$invoice, 'Stripe']);

        // Should create a new payment for only the remaining 40.00.
        $newPayment = Payment::where('invoice_id', $invoice->id)
            ->where('payment_status', 'success')
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($newPayment);
        $this->assertEqualsWithDelta(40.00, (float) $newPayment->amount, 0.01);
    }

    // --- clearCart() (private) ---

    public function test_clear_cart_deletes_items_and_resets_coupon(): void
    {
        $this->getLoggedInUser('user');

        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create(['user_id' => $this->user->id, 'grand_total' => 50.0]);

        // Create a cart for this user.
        /** @var Cart $cart */
        $cart = Cart::create([
            'user_id'          => $invoice->user_id,
            'coupon_code'      => 'PROMO10',
            'coupon_discount'  => 10.0,
            'currency'         => 'USD',
        ]);

        // Add items to the cart.
        CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => 1,
            'quantity'   => 1,
            'agents'     => 1,
        ]);

        $this->getPrivateMethod($this->service, 'clearCart', [$invoice]);

        $cart->refresh();
        $this->assertNull($cart->coupon_code);
        $this->assertSame(0, (int) $cart->coupon_discount);
        $this->assertSame(0, CartItem::where('cart_id', $cart->id)->count());
    }

    public function test_clear_cart_does_not_crash_when_no_cart_exists(): void
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->create(['grand_total' => 50.0]);

        // Invoice belongs to a user with no cart — must not throw.
        $this->getPrivateMethod($this->service, 'clearCart', [$invoice]);

        $this->assertTrue(true); // Reached here without exception
    }

    
}
