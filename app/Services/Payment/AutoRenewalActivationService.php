<?php

namespace App\Services\Payment;

use App\ApiKey;
use App\Auto_renewal;
use App\Http\Controllers\Common\PhpMailController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Model\Order\Order;
use App\Model\Payment\Plan;
use App\Model\Product\Product;
use App\Model\Product\Subscription;
use App\Traits\TaxCalculation;
use App\User;
use Exception;
use Illuminate\Database\QueryException;
use Logger;
use Throwable;

/**
 * Turns a saved, reusable payment reference into an active auto-renewal:
 * records it (Auto_renewal) and flips the order's Subscription auto-renew
 * flag on. For Razorpay, also creates the real gateway subscription right
 * away (it can't save a card for later off-session use the way Stripe can,
 * so the mandate has to be set up — and the customer asked to authorize it —
 * immediately rather than waiting for {@see \App\Console\Commands\RenewalCron}
 * to notice the subscription nearing expiry, which can otherwise be months
 * away). For Stripe, the card is already saved from this charge, so
 * RenewalCron creating the gateway subscription near expiry is enough.
 *
 * No Auth::user() dependency, since this runs from both an authenticated
 * request (the post-purchase "enable auto-renewal" flow) and an
 * unauthenticated one (a purchase confirmed via gateway webhook).
 */
class AutoRenewalActivationService
{
    use TaxCalculation;

    /**
     * Idempotent — a repeat call for the same order + gateway (e.g. both the
     * redirect-confirm and the webhook reaching this for one purchase) is a
     * no-op, so it's safe to call from more than one place.
     */
    public function activate(Order $order, User $user, string $gateway, string $paymentReference): void
    {
        if (! $this->claimActivation($order, $gateway, $user, $paymentReference)) {
            return;
        }

        $gatewayColumn = $gateway === 'razorpay' ? 'rzp_subscription' : 'autoRenew_status';
        Subscription::where('order_id', $order->id)->update([
            'is_subscribed' => '1',
            $gatewayColumn => '1',
        ]);

        new PhpMailController()->payment_log(
            $user->email, $gateway, 'success', $order->number, null, 0, 'Payment method updated'
        );

        if ($gateway === 'razorpay') {
            $this->createRazorpaySubscriptionNow($order, $user);
        }
    }

    /**
     * Never lets a failure here fail the caller — auto-renewal is a bonus,
     * not a requirement of a successful purchase or card verification.
     */
    private function createRazorpaySubscriptionNow(Order $order, User $user): void
    {
        try {
            $context = $this->resolveSubscriptionContext($order);
            if (! $context) {
                return;
            }
            ['subscription' => $subscription, 'plan' => $plan, 'product' => $product, 'invoice' => $invoice] = $context;

            ['unitCost' => $unitCost, 'currency' => $currency] = $this->razorpayRecurringUnitCost($subscription, $plan, $order, $user);

            resolve(SubscriptionController::class)->handleRazorpaySubscription(
                $unitCost, $plan, $product, $invoice, $currency,
                $subscription, $user, $order, $subscription->update_ends_at,
                immediate: true,
            );
        } catch (Throwable $throwable) {
            Logger::exception($throwable);
        }
    }

    /**
     * Creates a fresh Razorpay Subscription every time — always, no reuse —
     * so the browser can open Razorpay's Checkout widget directly against it
     * and the customer authorizes in one step, in the same popup, instead of
     * a separate verification charge followed by an emailed link to a second
     * page.
     *
     * Deliberately not reused across repeat calls (e.g. a page reload before
     * authorizing): an earlier version tried to reuse an existing
     * not-yet-authorized subscription, which turned out unsafe twice over —
     * once it handed back a subscription Razorpay had already cancelled
     * (a webhook-driven halt or a disable racing this call), and once it kept
     * handing back a subscription whose price had gone stale after a pricing
     * fix landed, since the amount is fixed into the Razorpay Plan at
     * creation and never rechecked. A handful of unused, never-authorized
     * "created" subscriptions left behind on Razorpay's side is a
     * pending-outcome-only side effect (nothing charges from them) — a much
     * cheaper cost than showing a customer a stale or dead price.
     *
     * @return array{subscription_id: string, key: string}
     *
     * @throws Exception
     */
    public function prepareRazorpaySubscriptionForAuthorization(Order $order, User $user): array
    {
        $context = $this->resolveSubscriptionContext($order);
        if (! $context) {
            throw new Exception(__('message.something_went_wrong'));
        }
        ['subscription' => $subscription, 'plan' => $plan, 'product' => $product, 'invoice' => $invoice] = $context;

        ['unitCost' => $unitCost, 'currency' => $currency] = $this->razorpayRecurringUnitCost($subscription, $plan, $order, $user);

        $response = resolve(RazorpayController::class)->handleRzpAutoPay(
            $unitCost, $plan->days, $product->name, $invoice, $currency,
            $subscription, $user, $order, $subscription->update_ends_at, $product,
            immediate: true,
        );

        Subscription::where('id', $subscription->id)->update([
            'subscribe_id' => $response->id,
            'rzp_subscription' => '2',
        ]);

        return [
            'subscription_id' => (string) $response->id,
            'key' => (string) (ApiKey::find(1)->rzp_key ?? ''),
        ];
    }

    /**
     * Records the authorization this subscription just confirmed synchronously
     * (via the Checkout popup) — goes straight to fully active, skipping the
     * "pending, waiting on the customer" state entirely since authorization is
     * already done by the time this is called.
     */
    public function activateConfirmedRazorpaySubscription(Order $order, User $user, string $paymentReference): void
    {
        if ($this->claimActivation($order, 'razorpay', $user, $paymentReference)) {
            new PhpMailController()->payment_log(
                $user->email, 'razorpay', 'success', $order->number, null, 0, 'Payment method updated'
            );
        }

        Subscription::where('order_id', $order->id)->update([
            'is_subscribed' => '1',
            'rzp_subscription' => '3',
        ]);
    }

    /**
     * Atomically claims the Auto_renewal row for this order+gateway via the
     * DB's own unique constraint (auto_renewals_order_gateway_unique) —
     * a plain exists()-then-create() has a real race window: two
     * near-simultaneous triggers for the same order+gateway (a redirect-
     * confirm racing a webhook) could both pass the exists() check before
     * either insert landed, producing duplicate rows and, for Razorpay, two
     * real gateway subscriptions from one purchase. This actually happened in
     * production (duplicate rows found there) before the constraint existed.
     * Returns true only if THIS call created the row — false if it already
     * existed, or if it lost the race to a concurrent call.
     */
    private function claimActivation(Order $order, string $gateway, User $user, string $paymentReference): bool
    {
        try {
            $autoRenewal = Auto_renewal::firstOrCreate(
                ['order_id' => $order->id, 'payment_method' => $gateway],
                ['user_id' => $user->id, 'customer_id' => $paymentReference, 'payment_intent_id' => $paymentReference]
            );
        } catch (QueryException $exception) {
            return false;
        }

        return $autoRenewal->wasRecentlyCreated;
    }

    /**
     * The Razorpay Plan resource created here fixes the amount charged on
     * every future automatic renewal — it must match, in full, what
     * {@see \App\Console\Commands\RenewalCron} would actually charge: base
     * renewal price, PLUS the gateway's processing fee, PLUS tax — exactly
     * the same three steps {@see \App\Http\Controllers\Subscription\SubscriptionController::processSubscriptionRenewal()}
     * applies before generating a renewal invoice. Skipping either the fee
     * or the tax here would under-charge every future auto-renewal forever
     * (and mismatch what a manually-renewed invoice for the same order would
     * total). No invoice is created here, unlike the cron — nothing is due
     * yet at enable time, so this only needs the *total*, computed the same
     * way, not an actual pending invoice sitting unpaid for months.
     *
     * @return array{unitCost: float, currency: string}
     */
    private function razorpayRecurringUnitCost(Subscription $subscription, Plan $plan, Order $order, User $user): array
    {
        $planDetails = userCurrencyAndPrice($user->id, $plan);
        $cost = resolve(SubscriptionController::class)->calculateRenewalCost($subscription, $planDetails, $order);
        $cost = ProcessingFee::addTo($cost, 'razorpay');

        $tax = $this->calculateTax($subscription->product_id, $user->state ?? '', $user->country ?? '');
        $cost = rounding($this->calculateTotal((string) $tax['value'], $cost));

        return [
            'unitCost' => calculateUnitCost($planDetails['currency'], $cost),
            'currency' => $planDetails['currency'],
        ];
    }

    /**
     * @return array{subscription: Subscription, plan: Plan, product: Product, invoice: \App\Model\Order\Invoice}|null
     */
    private function resolveSubscriptionContext(Order $order): ?array
    {
        $subscription = Subscription::where('order_id', $order->id)->first();
        $plan = $subscription ? Plan::find($subscription->plan_id) : null;
        $product = $subscription ? Product::find($subscription->product_id) : null;
        $invoice = $order->invoices()->latest()->first();

        if (! $subscription || ! $plan || ! $product || ! $invoice) {
            return null;
        }

        return compact('subscription', 'plan', 'product', 'invoice');
    }
}
