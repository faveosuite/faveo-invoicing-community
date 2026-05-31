<?php

namespace App\Services\Payment;

use App\Model\Payment\OpenPaymentOrder;
use App\Plugins\Payment\Dto\Customer;
use App\Plugins\Payment\Dto\PaymentRequest;
use App\Plugins\Payment\Dto\PaymentSession;

/**
 * Open-payment domain logic.
 *
 * Open payments are standalone, ad-hoc charges (an {@see OpenPaymentOrder}: a
 * name/email/amount with no invoice and no order fulfilment) — so "fulfilment"
 * here is simply marking the order paid. Like {@see InvoicePaymentService}, this
 * sits over the generalized {@see PaymentService}: it turns an order into a
 * package {@see PaymentRequest}, opens / verifies the payment through the same
 * gateways the rest of the app uses, and updates the order's status.
 *
 * The order id is threaded through the gateway metadata so an asynchronous
 * webhook can later locate and settle the same order.
 */
class OpenPaymentService
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    /** Stripe publishable key, for the client to initialise Stripe.js. */
    public function publishableKey(): string
    {
        return $this->payments->publishableKey();
    }

    /**
     * Open a payment for an order and return the gateway's client config.
     * Records the gateway's session/order id so a later callback can be matched.
     *
     * @throws \App\Plugins\Payment\Exceptions\PaymentException
     */
    public function start(OpenPaymentOrder $order): PaymentSession
    {
        $session = $this->payments->start($order->gateway, $this->orderRequest($order));

        $order->update(['gateway_transaction_id' => $session->id]);

        return $session;
    }

    /**
     * Verify a client callback for an order and mark it paid on success.
     *
     * Idempotent: an already-paid order short-circuits to true. Package
     * exceptions (PaymentException / SignatureVerificationException) propagate.
     *
     * @param  array<string, mixed>  $payload  Raw gateway callback fields.
     */
    public function confirm(OpenPaymentOrder $order, array $payload): bool
    {
        if ($order->isPaid()) {
            return true;
        }

        $result = $this->payments->capture($order->gateway, $payload);
        if (! $result->paid) {
            $order->update(['payment_status' => 'failed']);

            return false;
        }

        $this->markPaid($order, $result->reference);

        return true;
    }

    /**
     * Authenticate and process a gateway webhook for open payments.
     *
     * Returns true when the webhook was genuine and processed; false when the
     * signature could not be verified. Signature verification is delegated to the
     * package; the verified payload is then parsed for the order it settles.
     */
    public function handleWebhook(string $gateway, string $rawPayload, string $signature): bool
    {
        if (! $this->payments->verifyWebhook($gateway, $rawPayload, $signature)) {
            return false;
        }

        $event = json_decode($rawPayload, true) ?: [];

        strtolower($gateway) === 'stripe'
            ? $this->handleStripeEvent($event)
            : $this->handleRazorpayEvent($event);

        return true;
    }

    /** Settle / fail an order from a verified Stripe webhook event. */
    private function handleStripeEvent(array $event): void
    {
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];
        $orderId = $object['metadata']['order_id'] ?? null;

        if (! $orderId || ! $order = OpenPaymentOrder::find($orderId)) {
            return;
        }

        // Embedded Checkout completes via checkout.session.completed; the legacy
        // PaymentIntent event is still honoured for safety.
        if (in_array($type, ['checkout.session.completed', 'payment_intent.succeeded'], true)) {
            if (! $order->isPaid()) {
                $this->markPaid($order, $object['payment_intent'] ?? $object['id'] ?? null);
            }
        } elseif ($type === 'payment_intent.payment_failed' && ! $order->isPaid()) {
            $order->update(['payment_status' => 'failed']);
        }
    }

    /** Settle / fail an order from a verified Razorpay webhook event. */
    private function handleRazorpayEvent(array $event): void
    {
        $type = $event['event'] ?? null;
        $payment = $event['payload']['payment']['entity'] ?? [];
        $orderId = $payment['notes']['order_id'] ?? null;

        if (! $orderId || ! $order = OpenPaymentOrder::find($orderId)) {
            return;
        }

        if ($type === 'payment.captured' && ! $order->isPaid()) {
            $this->markPaid($order, $payment['id'] ?? null);
        } elseif ($type === 'payment.failed' && ! $order->isPaid()) {
            $order->update(['payment_status' => 'failed']);
        }
    }

    /** Mark an order paid, recording the gateway's transaction reference. */
    private function markPaid(OpenPaymentOrder $order, ?string $gatewayReference): void
    {
        $order->update([
            'payment_status' => 'completed',
            'gateway_transaction_id' => $gatewayReference ?: $order->gateway_transaction_id,
            'paid_at' => now(),
        ]);
    }

    /** Build the package payment request for an open-payment order. */
    private function orderRequest(OpenPaymentOrder $order): PaymentRequest
    {
        return new PaymentRequest(
            amount: (float) $order->amount,
            currency: $order->currency,
            reference: (string) $order->transaction_id,
            customer: new Customer(
                name: $order->name ?: null,
                email: $order->email ?: null,
                phone: $order->mobile ?: null,
                line1: $order->address ?: null,
                city: $order->city ?: null,
                state: $order->state ?: null,
                postalCode: $order->zip ?: null,
                country: $order->country ?: null,
            ),
            description: $order->description ?: 'Open Payment',
            metadata: ['order_id' => (int) $order->id],
        );
    }
}
