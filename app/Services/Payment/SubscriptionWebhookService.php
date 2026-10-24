<?php

namespace App\Services\Payment;

use App\Http\Controllers\ConcretePostSubscriptionHandleController;
use App\Http\Controllers\Order\BaseRenewController;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Payment\GatewayEvent;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Plugins\Payment\Support\Money;
use App\User;
use DB;
use Exception;
use Illuminate\Database\QueryException;
use Logger;
use Throwable;

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
    ) {
    }

    // ── Stripe ────────────────────────────────────────────────────────────

    /**
     * @param  array<mixed>  $event
     */
    public function handleStripeEvent(array $event): void
    {
        // Stripe guarantees only at-least-once delivery — every event carries
        // a stable id, so claim it once here (covers every Stripe event type
        // below, not just renewals) rather than redoing the same work on a
        // redelivery.
        $eventId = (string) ($event['id'] ?? '');
        if ($eventId !== '' && ! $this->claimEvent('stripe', $eventId)) {
            return;
        }

        $type = $event['type'] ?? null;
        $object = $event['data']['object'] ?? [];

        $this->releasingClaimOnFailure('stripe', $eventId, fn () => match ($type) {
            'invoice.payment_succeeded' => $this->onStripeInvoicePaid($object),
            'invoice.payment_failed' => $this->onStripeInvoiceFailed($object),
            'customer.subscription.deleted' => $this->onStripeSubscriptionDeleted($object),
            default => null,
        });
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

        $this->fulfillRenewal('stripe', $gatewaySubscriptionId, $amountPaid, $invoice['currency'] ?? null);
    }

    /**
     * Stripe fires this on EVERY failed charge attempt, including the very
     * first one — its own Smart Retries schedule may still recover this over
     * the next several days. Only `customer.subscription.deleted` (Stripe's
     * own dunning settings finally giving up) is the terminal signal;
     * deactivating here would pre-empt Stripe's automatic recovery, the same
     * mistake Razorpay's `subscription.halted` handler used to make. So this
     * only logs, for visibility — no state change.
     *
     * @param  array<mixed>  $invoice
     */
    private function onStripeInvoiceFailed(array $invoice): void
    {
        $gatewaySubscriptionId = $invoice['subscription'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        Logger::exception(new Exception(sprintf(
            'Stripe subscription %s payment attempt failed — gateway may still retry automatically', $gatewaySubscriptionId
        )));
    }

    /**
     * Stripe has already cancelled the subscription itself by the time this
     * fires — nothing left to cancel on our end, just mirror it locally.
     *
     * @param  array<mixed>  $stripeSubscription
     */
    private function onStripeSubscriptionDeleted(array $stripeSubscription): void
    {
        $gatewaySubscriptionId = $stripeSubscription['id'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        // Stripe's subscription id is dead for good once this fires — safe to clear.
        $this->deactivateAfterTerminalFailure($gatewaySubscriptionId, 'stripe', 'Stripe subscription cancelled after repeated payment failures', clearSubscribeId: true);
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
            'subscription.pending' => $this->onRazorpayPending($event['payload'] ?? []),
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
        $paymentId = (string) ($payload['payment']['entity']['id'] ?? '');

        if (! $gatewaySubscriptionId) {
            return;
        }

        // Razorpay's payload (unlike Stripe's) carries no top-level event id,
        // but a fresh payment id is generated for every real charge — a
        // redelivery of the same charge event carries the same one, so it's
        // a reliable claim key here even without one.
        if ($paymentId !== '' && ! $this->claimEvent('razorpay', $paymentId)) {
            return;
        }

        $this->releasingClaimOnFailure('razorpay', $paymentId, fn () => $this->fulfillRenewal('razorpay', $gatewaySubscriptionId, $amountPaid, $payload['payment']['entity']['currency'] ?? null));
    }

    /**
     * The first failed auto-charge — Razorpay auto-retries on T+1/T+2/T+3
     * before giving up and firing `subscription.halted`. Too early to act on;
     * just logged so a payment-health check has something to find instead of
     * this being invisible until it's already halted.
     *
     * @param  array<mixed>  $payload
     */
    private function onRazorpayPending(array $payload): void
    {
        $gatewaySubscriptionId = $payload['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        Logger::exception(new Exception(sprintf(
            'Razorpay subscription %s payment attempt failed — gateway will retry automatically', $gatewaySubscriptionId
        )));
    }

    /**
     * All of Razorpay's own retries (T+1/T+2/T+3) have failed. Deliberately
     * does NOT cancel the subscription at Razorpay — `halted` can still
     * recover to `active` on its own if the customer updates their card via
     * the link Razorpay emails them, and cancelling here would throw that
     * recovery away permanently.
     *
     * @param  array<mixed>  $payload
     */
    private function onRazorpayHalted(array $payload): void
    {
        $gatewaySubscriptionId = $payload['subscription']['entity']['id'] ?? null;
        if (! $gatewaySubscriptionId) {
            return;
        }

        // Razorpay redelivers on a non-2xx response; unlike `charged`, a
        // halt carries no payment id to key off, so the subscription id
        // itself (namespaced by event type) is the claim key here.
        if (! $this->claimEvent('razorpay', 'halted:'.$gatewaySubscriptionId)) {
            return;
        }

        // Razorpay can still bring this subscription id back to `active` on
        // its own — the subscribe_id must survive so a later
        // `subscription.charged` for it still matches and gets fulfilled.
        $this->deactivateAfterTerminalFailure($gatewaySubscriptionId, 'razorpay', 'Razorpay subscription payment halted', clearSubscribeId: false);
    }

    /**
     * Terminal payment-failure handling shared by both gateways: Razorpay's
     * `subscription.halted` (its own retries exhausted) and Stripe's
     * `customer.subscription.deleted` (Stripe's own dunning settings gave
     * up). Neither needs — or should get — a cancel call: Razorpay still
     * owns halted's recovery path, and Stripe has already cancelled the
     * subscription itself by the time its event fires.
     */
    private function deactivateAfterTerminalFailure(string $gatewaySubscriptionId, string $gateway, string $exceptionMessage, bool $clearSubscribeId): void
    {
        $subscription = Subscription::where('subscribe_id', $gatewaySubscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $order = Order::find($subscription->order_id);
        $user = User::find($subscription->user_id);
        $product = Product::find($subscription->product_id);

        $this->handler->sendFailedPayment(
            total: null, exceptionMessage: $exceptionMessage, user: $user,
            number: (string) $order?->number, end: (string) $subscription->update_ends_at,
            currency: '', order: $order, product_details: $product, invoice: null, payment: $gateway,
            cancelAtGateway: false,
            clearSubscribeId: $clearSubscribeId,
        );
    }

    // ── Fulfillment ───────────────────────────────────────────────────────

    private function fulfillRenewal(string $gateway, string $gatewaySubscriptionId, int $gatewayAmount, ?string $gatewayCurrency = null): void
    {
        $subscription = Subscription::where('subscribe_id', $gatewaySubscriptionId)->first();
        if (! $subscription) {
            Logger::exception(new Exception(sprintf('SubscriptionWebhook: no subscription found for %s ID %s', $gateway, $gatewaySubscriptionId)));

            return;
        }

        $order = Order::findOrFail($subscription->order_id);
        $user = User::findOrFail($subscription->user_id);
        $plan = Plan::findOrFail($subscription->plan_id);
        $product = Product::findOrFail($subscription->product_id);

        // The currency the gateway actually charged in — the user's country can have changed
        // since they subscribed, and minor units read in the wrong currency are a wrong amount.
        $currency = $gatewayCurrency ? strtoupper($gatewayCurrency) : userCurrencyAndPrice($user->id, $plan)['currency'];

        // Convert gateway amount (smallest unit) to local currency amount
        $cost = $this->fromGatewayAmount($gatewayAmount, $currency);

        $invoice = $this->findOrCreateRenewalInvoice($subscription, $order, $product, $user, $plan, $cost, $currency);

        $sub = $this->handler->successRenew($invoice, $subscription, $gateway, $currency);
        $this->handler->recordPayment($invoice, $gateway);

        if (emailSendingStatus()) {
            $this->handler->sendPaymentSuccessMail($sub, $currency, $cost, $user, $product->name, $order->number); // @phpstan-ignore argument.type
            $this->handler->PaymentSuccessMailtoAdmin($invoice, $cost, $user, $product->name, order: $order, payment: $gateway);
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

        $taxRate = 0.0;
        try {
            $taxInfo = new BaseRenewController()->calculateTax($product->id, $user->state ?? '', $user->country ?? '');
            $taxRate = (float) $taxInfo['value'];
        } catch (\Throwable $e) {
            \Logger::exception($e);
        }

        // $cost from gateway is gross (tax-inclusive). generateInvoice() calculates tax on top of
        // the cost passed in, so we pass the net base amount to keep the invoice grand_total
        // matching the actual charged gateway amount.
        $netCost = $taxRate > 0 ? (float) rounding($cost / (1 + ($taxRate / 100))) : $cost;

        $latestInvoiceId = DB::table('order_invoice_relations')
            ->where('order_id', $order->id)
            ->latest('id')
            ->value('invoice_id');

        $agents = $latestInvoiceId
            ? DB::table('invoice_items')->where('invoice_id', $latestInvoiceId)->whereNotNull('agents')->value('agents')
            : null;

        if (! $agents && ! empty($order->serial_key)) {
            $license = (string) $order->serial_key;
            $agentStr = ltrim(substr($license, -4), '0');
            if ($agentStr !== '') {
                $agents = (int) $agentStr;
            }
        }

        if (! $agents) {
            $originalInvoiceId = DB::table('order_invoice_relations')
                ->where('order_id', $order->id)
                ->oldest('id')
                ->value('invoice_id');
            $agents = $originalInvoiceId ? DB::table('invoice_items')->where('invoice_id', $originalInvoiceId)->value('agents') : null;
        }

        $invoiceItem = new BaseRenewController()->generateInvoice($product, $user, $order->id, $plan->id, $netCost, '', $agents, $currency);

        return Invoice::findOrFail($invoiceItem->invoice_id); // @phpstan-ignore property.notFound
    }

    /**
     * Convert gateway's smallest-unit amount to the local decimal amount.
     * Uses Money::fromMinor to properly respect ISO-4217 exponents (including ISK, KWD, JPY, etc.).
     */
    private function fromGatewayAmount(int $amount, string $currency): float
    {
        return Money::fromMinor($amount, $currency);
    }

    /**
     * Atomically claims a gateway event id — true the first time it's seen,
     * false on a repeat (a losing/duplicate insert throws on the unique
     * constraint, caught here rather than a check-then-act race).
     */
    /**
     * Run the work for a claimed event; if it fails, drop the claim and rethrow so the
     * gateway's retry (it sees the error) can do it — otherwise the retry hit the claim, got a
     * 200 and nothing was ever done: charged, not renewed.
     */
    private function releasingClaimOnFailure(string $gateway, string $eventId, callable $work): void
    {
        try {
            $work();
        } catch (Throwable $throwable) {
            if ($eventId !== '') {
                GatewayEvent::where(['gateway' => $gateway, 'event_id' => $eventId])->delete();
            }

            throw $throwable;
        }
    }

    private function claimEvent(string $gateway, string $eventId): bool
    {
        try {
            GatewayEvent::create(['gateway' => $gateway, 'event_id' => $eventId]);

            return true;
        } catch (QueryException) {
            return false;
        }
    }
}
