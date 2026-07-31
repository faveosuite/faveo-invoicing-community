<?php

namespace App\Services\Payment;

use App\Model\Order\Invoice;
use App\Model\Payment\OpenPaymentOrder;

class WebhookDispatcher
{
    /**
     * @var array<mixed>
     */
    private array $handlers = [];

    /**
     * @param  array<mixed>  $events
     */
    public function on(string|array $events, callable $handler): static
    {
        foreach ((array) $events as $event) {
            $this->handlers[$event] = $handler;
        }

        return $this;
    }

    /**
     * @param  array<mixed>  $event
     */
    public function dispatch(string $eventType, array $event): void
    {
        ($this->handlers[$eventType] ?? static fn (): null => null)($event);
    }

    // ── Pre-configured dispatchers ────────────────────────────────────────

    public static function stripe(): self
    {
        return (new self)
            ->on(
                ['invoice.payment_succeeded', 'invoice.payment_failed', 'customer.subscription.deleted'],
                fn (array $e) => resolve(SubscriptionWebhookService::class)->handleStripeEvent($e)
            )
            ->on(
                ['checkout.session.completed', 'payment_intent.succeeded'],
                fn ($e) => self::confirmStripePayment($e['data']['object'] ?? [])
            )
            ->on(
                'payment_intent.payment_failed',
                fn ($e) => self::failStripePayment($e['data']['object'] ?? [])
            );
    }

    public static function razorpay(): self
    {
        return (new self)
            ->on(
                ['subscription.charged', 'subscription.pending', 'subscription.halted'],
                fn (array $e) => resolve(SubscriptionWebhookService::class)->handleRazorpayEvent($e)
            )
            ->on(
                ['payment.captured', 'payment.failed'],
                fn (array $e) => self::handleRazorpayPayment($e)
            );
    }

    // ── Stripe payment handlers ───────────────────────────────────────────

    /**
     * @param  array<mixed>  $object
     */
    private static function confirmStripePayment(array $object): void
    {
        if ($invoiceId = $object['metadata']['invoice_id'] ?? null) {
            /** @var Invoice|null $invoice */
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                resolve(InvoicePaymentService::class)->confirm($invoice, 'Stripe', [
                    'payment_intent' => $object['payment_intent'] ?? $object['id'] ?? null,
                    'credit_applied' => $object['metadata']['credit_applied'] ?? 0,
                ]);
            }

            return;
        }

        $orderId = $object['metadata']['order_id'] ?? null;
        /** @var OpenPaymentOrder|null $stripeOrder */
        $stripeOrder = $orderId ? OpenPaymentOrder::find($orderId) : null;
        if ($stripeOrder && ! $stripeOrder->isPaid()) {
            $stripeOrder->update([
                'payment_status' => 'completed',
                'gateway_transaction_id' => $object['payment_intent'] ?? $object['id'] ?? null,
                'paid_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<mixed>  $object
     */
    private static function failStripePayment(array $object): void
    {
        $orderId = $object['metadata']['order_id'] ?? null;
        /** @var OpenPaymentOrder|null $failStripeOrder */
        $failStripeOrder = $orderId ? OpenPaymentOrder::find($orderId) : null;
        if ($failStripeOrder && ! $failStripeOrder->isPaid()) {
            $failStripeOrder->update(['payment_status' => 'failed']);
        }
    }

    // ── Razorpay payment handlers ─────────────────────────────────────────

    /**
     * @param  array<mixed>  $event
     */
    private static function handleRazorpayPayment(array $event): void
    {
        $payment = $event['payload']['payment']['entity'] ?? [];
        $type = $event['event'] ?? '';

        if ($invoiceId = $payment['notes']['invoice_id'] ?? null) {
            /** @var Invoice|null $invoice2 */
            $invoice2 = Invoice::find($invoiceId);
            if ($invoice2 && $type === 'payment.captured') {
                resolve(InvoicePaymentService::class)->confirm($invoice2, 'Razorpay', [
                    'razorpay_payment_id' => $payment['id'] ?? null,
                    'credit_applied' => $payment['notes']['credit_applied'] ?? 0,
                ]);
            }

            return;
        }

        $orderId = $payment['notes']['order_id'] ?? null;
        /** @var OpenPaymentOrder|null $rzpOrder */
        $rzpOrder = $orderId ? OpenPaymentOrder::find($orderId) : null;
        if ($rzpOrder) {
            if ($type === 'payment.captured' && ! $rzpOrder->isPaid()) {
                $rzpOrder->update([
                    'payment_status' => 'completed',
                    'gateway_transaction_id' => $payment['id'] ?? null,
                    'paid_at' => now(),
                ]);
            } elseif ($type === 'payment.failed' && ! $rzpOrder->isPaid()) {
                $rzpOrder->update(['payment_status' => 'failed']);
            }
        }
    }
}
