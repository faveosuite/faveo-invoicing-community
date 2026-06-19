<?php

namespace App\Services\Payment;

use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\User;
use DB;
use Logger;

/**
 * Handles subscription renewal fulfillment triggered by gateway webhooks.
 *
 * Stripe fires invoice.payment_succeeded on each subscription cycle.
 * Razorpay fires subscription.charged on each subscription cycle.
 *
 * Both events carry the gateway subscription ID, which we use to locate
 * our Subscription record and fulfill the renewal — no polling, no manual
 * amount reconciliation.
 */
class SubscriptionWebhookService
{
    public function __construct(
        private readonly ConcretePostSubscriptionHandleController $handler
    ) {}

    // ── Stripe ────────────────────────────────────────────────────────────

    /**
     * @param  array<mixed>  $event
     */
    public function handleStripeEvent(array $event): void
    {
        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];

        match ($type) {
            'invoice.payment_succeeded' => $this->onStripeInvoicePaid($object),
            'invoice.payment_failed' => $this->onStripeInvoiceFailed($object),
            'customer.subscription.deleted' => $this->onStripeSubscriptionDeleted($object),
            default => null,
        };
    }

    /**
     * @param  array<mixed>  $invoice
     */
    private function onStripeInvoicePaid(array $invoice): void
    {
        // Only handle subscription renewal cycles, not the initial charge
        if (($invoice['billing_reason'] ?? '') !== 'subscription_cycle') {
            return;
        }

        $gatewaySubscriptionId = $invoice['subscription'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        $amountPaid = $invoice['amount_paid'] ?? 0;

        $this->fulfillRenewal('stripe', $gatewaySubscriptionId, $amountPaid);
    }

    /**
     * @param  array<mixed>  $invoice
     */
    private function onStripeInvoiceFailed(array $invoice): void
    {
        $gatewaySubscriptionId = $invoice['subscription'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('subscribe_id', $gatewaySubscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $this->handler->disableAutorenewalStatusByOrderId($subscription->order_id);

        /** @var Order|null $order */
        $order = Order::find($subscription->order_id);
        /** @var User|null $user */
        $user = User::find($subscription->user_id);
        $product = Product::find($subscription->product_id);

        $this->handler->sendFailedPayment(
            total: null, exceptionMessage: 'Stripe subscription payment failed', user: $user,
            number: (string) $order?->number, end: (string) $subscription->update_ends_at,
            currency: $invoice['currency'] ?? '', order: $order, product_details: $product, invoice: null, payment: 'stripe'
        );
    }

    /**
     * @param  array<mixed>  $stripeSubscription
     */
    private function onStripeSubscriptionDeleted(array $stripeSubscription): void
    {
        $subscription = Subscription::where('subscribe_id', $stripeSubscription['id'] ?? '')->first();
        if ($subscription) {
            $subscription->update(['is_subscribed' => 0, 'autoRenew_status' => 0, 'subscribe_id' => '']);
        }
    }

    // ── Razorpay ──────────────────────────────────────────────────────────

    /**
     * @param  array<mixed>  $event
     */
    public function handleRazorpayEvent(array $event): void
    {
        $type = $event['event'] ?? null;

        match ($type) {
            'subscription.charged' => $this->onRazorpayCharged($event['payload'] ?? []),
            'subscription.halted' => $this->onRazorpayHalted($event['payload'] ?? []),
            default => null,
        };
    }

    /**
     * @param  array<mixed>  $payload
     */
    private function onRazorpayCharged(array $payload): void
    {
        $gatewaySubscriptionId = $payload['subscription']['entity']['id'] ?? null;
        $amountPaid = $payload['payment']['entity']['amount'] ?? 0;

        if (! $gatewaySubscriptionId) {
            return;
        }

        $this->fulfillRenewal('razorpay', $gatewaySubscriptionId, $amountPaid);
    }

    /**
     * @param  array<mixed>  $payload
     */
    private function onRazorpayHalted(array $payload): void
    {
        $gatewaySubscriptionId = $payload['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        $subscription = Subscription::where('subscribe_id', $gatewaySubscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $this->handler->disableAutorenewalStatusByOrderId($subscription->order_id);

        $order = Order::find($subscription->order_id);
        $user = User::find($subscription->user_id);
        $product = Product::find($subscription->product_id);

        $this->handler->sendFailedPayment(
            total: null, exceptionMessage: 'Razorpay subscription payment halted', user: $user,
            number: (string) $order?->number, end: (string) $subscription->update_ends_at,
            currency: '', order: $order, product_details: $product, invoice: null, payment: 'razorpay'
        );
    }

    // ── Fulfillment ───────────────────────────────────────────────────────

    private function fulfillRenewal(string $gateway, string $gatewaySubscriptionId, int $gatewayAmount): void
    {
        $subscription = Subscription::where('subscribe_id', $gatewaySubscriptionId)->first();
        if (! $subscription) {
            Logger::warning(sprintf('SubscriptionWebhook: no subscription found for %s ID %s', $gateway, $gatewaySubscriptionId)); // @phpstan-ignore staticMethod.notFound

            return;
        }

        $order = Order::findOrFail($subscription->order_id);
        $user = User::findOrFail($subscription->user_id);
        $plan = Plan::findOrFail($subscription->plan_id);
        $product = Product::findOrFail($subscription->product_id);

        $planDetails = userCurrencyAndPrice($user->id, $plan);
        $currency = $planDetails['currency'];

        // Convert gateway amount (smallest unit) to local currency amount
        $cost = $this->fromGatewayAmount($gatewayAmount, $currency);

        $invoice = $this->findOrCreateRenewalInvoice($subscription, $order, $product, $user, $plan, $cost, $currency);

        $sub = $this->handler->successRenew($invoice, $subscription, $gateway, $currency);
        $this->handler->recordPayment($invoice, $gateway);

        if (emailSendingStatus()) {
            $this->handler->sendPaymentSuccessMail($sub, $currency, $cost, $user, $product->name, $order->number); // @phpstan-ignore argument.type
            $this->handler->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product->name, template: null, order: $order, payment: $gateway);
        }
    }

    private function findOrCreateRenewalInvoice(Subscription $subscription, Order $order, Product $product, User $user, Plan $plan, float $cost, string $currency): Invoice
    {
        $latestInvoiceId = DB::table('order_invoice_relations')
            ->where('order_id', $subscription->order_id)
            ->latest()
            ->value('invoice_id');

        $existingItem = DB::table('invoice_items')
            ->where('invoice_id', $latestInvoiceId)
            ->where('product_id', $subscription->product_id)
            ->first();

        if ($existingItem) {
            $unpaid = Invoice::where('id', $existingItem->invoice_id)
                ->where('status', 'pending')
                ->where('is_renewed', 1)
                ->latest()
                ->first();

            if ($unpaid) {
                return $unpaid;
            }
        }

        $originalInvoiceId = DB::table('order_invoice_relations')
            ->where('order_id', $order->id)
            ->oldest()
            ->value('invoice_id');

        $agents = DB::table('invoice_items')->where('invoice_id', $originalInvoiceId)->value('agents');
        $invoiceItem = new BaseRenewController()->generateInvoice($product, $user, $order->id, $plan->id, $cost, '', $agents, $currency);

        return Invoice::findOrFail($invoiceItem->invoice_id); // @phpstan-ignore property.notFound
    }

    /**
     * Convert gateway's smallest-unit amount to the local decimal amount.
     * Stripe/Razorpay send 1000 for $10.00 USD, 1000 for ¥1000 JPY, 1000 for 1.000 KWD.
     */
    private function fromGatewayAmount(int $amount, string $currency): float
    {
        $zeroDecimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];
        $threeDecimal = ['BHD', 'JOD', 'KWD', 'OMR', 'TND'];

        if (in_array($currency, $zeroDecimal, strict: true)) {
            return (float) $amount;
        }

        if (in_array($currency, $threeDecimal, strict: true)) {
            return round($amount / 1000, 3);
        }

        return round($amount / 100, 2);
    }
}
