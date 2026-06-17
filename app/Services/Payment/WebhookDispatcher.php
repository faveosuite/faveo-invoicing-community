<?php

namespace App\Services\Payment;

use App\Model\Order\Invoice;
use App\Model\Payment\OpenPaymentOrder;

class WebhookDispatcher
{
    private array $handlers = [];

    public function on(string|array $events, callable $handler): static
    {
        foreach ((array) $events as $event) {
            $this->handlers[$event] = $handler;
        }

        return $this;
    }

    public function dispatch(string $eventType, array $event): void
    {
        ($this->handlers[$eventType] ?? static fn (): null => null)($event);
    }

    // ── Pre-configured dispatchers ────────────────────────────────────────

    public static function stripe(): static
    {
        return (new static)
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

    public static function razorpay(): static
    {
        return (new static)
            ->on(
                ['subscription.charged', 'subscription.halted'],
                fn (array $e) => resolve(SubscriptionWebhookService::class)->handleRazorpayEvent($e)
            )
            ->on(
                ['payment.captured', 'payment.failed'],
                fn (array $e) => self::handleRazorpayPayment($e)
            );
    }

    // ── Stripe payment handlers ───────────────────────────────────────────

    private static function confirmStripePayment(array $object): void
    {
        if ($invoiceId = $object['metadata']['invoice_id'] ?? null) {
            if ($invoice = Invoice::find($invoiceId)) {
                resolve(InvoicePaymentService::class)->confirm($invoice, 'Stripe', [
                    'payment_intent' => $object['payment_intent'] ?? $object['id'] ?? null,
                ]);
            }

            return;
        }

        $orderId = $object['metadata']['order_id'] ?? null;
        if ($orderId && ($order = OpenPaymentOrder::find($orderId)) && ! $order->isPaid()) {
            $order->update([
                'payment_status' => 'completed',
                'gateway_transaction_id' => $object['payment_intent'] ?? $object['id'] ?? null,
                'paid_at' => now(),
            ]);
        }
    }

    private static function failStripePayment(array $object): void
    {
        $orderId = $object['metadata']['order_id'] ?? null;
        if ($orderId && ($order = OpenPaymentOrder::find($orderId)) && ! $order->isPaid()) {
            $order->update(['payment_status' => 'failed']);
        }
    }

    // ── Razorpay payment handlers ─────────────────────────────────────────

    private static function handleRazorpayPayment(array $event): void
    {
        $payment = $event['payload']['payment']['entity'] ?? [];
        $type = $event['event'] ?? '';

        if ($invoiceId = $payment['notes']['invoice_id'] ?? null) {
            if (($invoice = Invoice::find($invoiceId)) && $type === 'payment.captured') {
                resolve(InvoicePaymentService::class)->confirm($invoice, 'Razorpay', [
                    'razorpay_payment_id' => $payment['id'] ?? null,
                ]);
            }

            return;
        }

        $orderId = $payment['notes']['order_id'] ?? null;
        if ($orderId && $order = OpenPaymentOrder::find($orderId)) {
            if ($type === 'payment.captured' && ! $order->isPaid()) {
                $order->update([
                    'payment_status' => 'completed',
                    'gateway_transaction_id' => $payment['id'] ?? null,
                    'paid_at' => now(),
                ]);
            } elseif ($type === 'payment.failed' && ! $order->isPaid()) {
                $order->update(['payment_status' => 'failed']);
            }
        }
    }
}
