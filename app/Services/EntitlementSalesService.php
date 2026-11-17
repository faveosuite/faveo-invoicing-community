<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Controllers\Order\InvoiceController;
use App\Model\Order\CreditTransaction;
use App\Model\Order\Invoice;
use App\Model\Order\Order;
use App\Model\Order\OrderEntitlement;
use App\Model\Payment\Plan;
use App\Model\Payment\PlanPrice;
use App\Model\Product\CloudProducts;
use App\Model\Product\Entitlement;
use App\Model\Product\Subscription;
use App\Services\Payment\CreditBalanceService;
use App\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Selling extras on top of a plan: more units of a number (storage, policies) or an on/off
 * feature. Bought extras are kept in order_entitlements at the price paid, added to what the
 * license grants (EntitlementService), and billed at every renewal (renewalCost()).
 *
 *  - Buying applies now; the customer pays for the days left in the current period.
 *  - Reducing or cancelling applies at the next renewal; nothing is refunded.
 *
 * The purchase invoice is deliberately NOT linked to the order through order_invoice_relations
 * (same as plan-change invoices): the renewal agent count is read from the newest invoice line
 * linked to an order, and an extras line must not become that line.
 */
class EntitlementSalesService
{
    public const string INVOICE_TYPE = 'entitlement_purchase';

    /**
     * What the customer pays now for `$quantity` more units, before tax.
     *
     * @return array{amount: float, currency: string, period_id: int|null, unit_price: float, renew_unit_price: float, days_left: int, plan_days: int, plan_id: int, ends_at: string}
     */
    public function quote(Order $order, Entitlement $item, int $quantity): array
    {
        $subscription = $this->sellableSubscription($order, $item);
        $owned = OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $item->id)->exists();

        $reason = match (true) {
            $quantity < 1 || $quantity > 9999 => 'extra_quantity_range',
            $item->type === 'boolean' && $quantity !== 1 => 'extra_quantity_range',
            $item->type === 'boolean' && ($owned || $this->hasOpenInvoice($order, $item)) => 'extra_already_owned',
            default => null,
        };
        if ($reason) {
            throw ValidationException::withMessages(['quantity' => __("message.$reason")]);
        }

        $currency = $this->currencyFor($order, $subscription);
        $plan = Plan::find($subscription->plan_id);
        $periodId = $plan?->periodId();
        $unitPrice = $this->unitPrice($order, $item, $currency, $plan);
        $renewUnitPrice = $this->unitPrice($order, $item, $currency, $plan, 'renewal');
        $planDays = max(1, (int) Plan::whereKey($subscription->plan_id)->value('days'));
        $daysLeft = min($planDays, max(0, (int) Date::now()->diffInDays($subscription->termEndsAt(), absolute: false)));

        return [
            'amount' => round($unitPrice * $quantity * $daysLeft / $planDays, 2),
            'currency' => $currency,
            'period_id' => $periodId,
            'unit_price' => $unitPrice,
            'renew_unit_price' => $renewUnitPrice,
            'days_left' => $daysLeft,
            'plan_days' => $planDays,
            'plan_id' => (int) $subscription->plan_id,
            'ends_at' => (string) $subscription->termEndsAt(),
        ];
    }

    /**
     * The plan's price for a number of agents. The plan price covers its included agents; each
     * agent more or fewer is priced at the plan's Agents price (falls back to "plan price ÷
     * included agents"). Unlimited plans and an unlimited choice (0 agents) are the plan price.
     * A plan that pays for every agent charges its price plus all agents.
     *
     * @param  'purchase'|'renewal'  $kind
     */
    public function planCost(PlanPrice $planPrice, int $agents, string $kind): float
    {
        $base = $kind === 'renewal'
            ? (float) $planPrice->renew_price
            : (float) $planPrice->add_price * (1 - (float) ($planPrice->offer_price ?? 0) / 100);

        if (EntitlementService::paysForEveryAgent($planPrice->plan)) {
            return $base + max(1, $agents) * (float) $this->agentUnitPrice($planPrice, $kind);
        }

        $included = (int) $planPrice->no_of_agents;
        if ($agents <= 0 || $included <= 0 || $agents === $included) {
            return $base;
        }

        $share = $base / $included; // what one included agent is worth in the plan price
        $unit = $this->agentUnitPrice($planPrice, $kind) ?? $share;
        if ($agents < $included) {
            // Fewer agents take off at most each agent's share, so the line never drops below 0
            // (an agent price above the share would make it negative and discount the rest of the cart).
            $unit = min($unit, $share);
        }

        return $base + ($agents - $included) * $unit;
    }

    /**
     * One agent's price on this plan in its currency (the plan's Agents price).
     *
     * @param  'purchase'|'renewal'|'list'  $kind
     */
    public function agentUnitPrice(PlanPrice $planPrice, string $kind): ?float
    {
        $plan = $planPrice->plan;
        if (! $plan) {
            return null;
        }

        return Entitlement::with('prices')->where('feature', 'agents')->where('key', 'max')->first()
            ?->priceIn((string) $planPrice->currency, $plan, $kind);
    }

    /**
     * Whether this plan price can be charged. A plan that charges for every agent needs an agent
     * price in the currency: without one each agent would cost 0, so any number would be free.
     */
    public function canPrice(PlanPrice $planPrice): bool
    {
        return ! EntitlementService::paysForEveryAgent($planPrice->plan) || $this->agentUnitPrice($planPrice, 'purchase') !== null;
    }

    /** Creates the unpaid invoice for buying extras. Paying it runs fulfil(). */
    public function createPurchaseInvoice(Order $order, Entitlement $item, int $quantity): Invoice
    {
        $quote = $this->quote($order, $item, $quantity);
        if ($quote['amount'] <= 0) {
            throw ValidationException::withMessages(['quantity' => __('message.extra_nothing_to_pay')]);
        }

        /** @var User $user */
        $user = User::findOrFail($order->client);
        $controller = new InvoiceController;
        $tax = $controller->calculateTax((int) $order->product, (string) $user->state, (string) $user->country);

        return DB::transaction(function () use ($order, $item, $quantity, $quote, $user, $controller, $tax): Invoice {
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'number' => random_int(11111111, 99999999),
                'date' => Date::now(),
                'grand_total' => rounding($controller->calculateTotal((string) $tax['value'], $quote['amount'])),
                'currency' => $quote['currency'],
                'status' => 'pending',
                'metadata' => [
                    'type' => self::INVOICE_TYPE,
                    'order_id' => $order->id,
                    'entitlement_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $quote['unit_price'],
                    'renew_unit_price' => $quote['renew_unit_price'],
                    'period_id' => $quote['period_id'],
                    // The term the amount was prorated for: fulfil() checks it still holds.
                    'plan_id' => $quote['plan_id'],
                    'ends_at' => $quote['ends_at'],
                ],
            ]);

            $controller->createInvoiceItemsByAdmin(
                $invoice->id, (string) $order->product, $quote['amount'], $quote['currency'], 1, null,
                null, $user->id, $tax['name'], (float) $tax['value'], $quote['amount'],
            );
            // The line is named after the extra, not the product, so the invoice reads right.
            $invoice->invoiceItem()->update(['product_name' => $item->label.' × '.$quantity]);

            return $invoice;
        });
    }

    /** Adds a paid invoice's extras to its order. Safe to call twice for the same invoice. */
    public function fulfil(Invoice $invoice): void
    {
        $meta = $invoice->metadata ?? [];
        if (! empty($meta['applied'])) {
            return;
        }

        // Priced for a term that has since changed (renewed, plan changed, order closed, feature
        // archived): applying it would give the extra for the new term at the old prorated price,
        // or to a closed order. The payment goes to the customer's credit instead.
        if ($this->isStale($meta)) {
            $this->creditInstead($invoice, $meta, 'message.extra_invoice_stale_credited');

            return;
        }

        DB::transaction(function () use ($invoice, $meta): void {
            $extra = OrderEntitlement::lockForUpdate()->firstOrNew([
                'order_id' => (int) $meta['order_id'],
                'entitlement_id' => (int) $meta['entitlement_id'],
            ]);
            $quantity = (int) $meta['quantity'];

            // An on/off feature is owned once: a second paid invoice for it is credited back, not
            // stored as quantity 2 (which would bill twice at every renewal).
            if ($extra->exists && Entitlement::whereKey($extra->entitlement_id)->value('type') === 'boolean') {
                $this->creditInstead($invoice, $meta, 'message.extra_already_owned_credited');

                return;
            }

            if (! $extra->exists) {
                // What renews each period is the renewal price, kept for this customer.
                $extra->unit_price = (string) ($meta['renew_unit_price'] ?? $meta['unit_price']);
                $extra->currency = $invoice->currency;
                $extra->period_id = isset($meta['period_id']) ? (int) $meta['period_id'] : null;
                $extra->quantity = 0;
            }
            $extra->quantity += $quantity;
            if ($extra->renew_quantity !== null) {
                $extra->renew_quantity += $quantity; // bought more after reducing: those units renew too
            }
            $extra->save();

            $invoice->update(['metadata' => [...$meta, 'applied' => true]]);
        });
    }

    /**
     * The extras invoice is paid but can't be applied: its payment goes to the customer's credit.
     *
     * @param  array<string, mixed>  $meta
     */
    private function creditInstead(Invoice $invoice, array $meta, string $reason): void
    {
        DB::transaction(function () use ($invoice, $meta, $reason): void {
            resolve(CreditBalanceService::class)->grant(
                (int) $invoice->user_id, (string) $invoice->currency, $invoice->paidTotal(), CreditTransaction::TYPE_OVERPAYMENT,
                invoiceId: $invoice->id, note: __($reason),
            );
            $invoice->update(['metadata' => [...$meta, 'applied' => true, 'credited' => true]]);
        });
    }

    /** An unpaid invoice already raised for this extra on this order. */
    private function hasOpenInvoice(Order $order, Entitlement $item): bool
    {
        return Invoice::where('status', '!=', 'success')
            ->where('metadata->type', self::INVOICE_TYPE)
            ->where('metadata->order_id', $order->id)
            ->where('metadata->entitlement_id', $item->id)
            ->exists();
    }

    /**
     * Whether an extras invoice no longer matches its order. Invoices made before the term was
     * recorded (no ends_at) are only checked for a closed order or archived feature.
     *
     * @param  array<string, mixed>  $meta
     */
    private function isStale(array $meta): bool
    {
        $order = Order::find((int) ($meta['order_id'] ?? 0));
        $subscription = $order ? Subscription::where('order_id', $order->id)->first() : null;
        if (! $order || ! $subscription || $order->order_status === 'Terminated'
            || ! Entitlement::whereKey((int) ($meta['entitlement_id'] ?? 0))->where('is_active', true)->exists()) {
            return true;
        }

        return isset($meta['ends_at'])
            && ((string) $subscription->termEndsAt() !== $meta['ends_at'] || (int) $subscription->plan_id !== (int) $meta['plan_id']);
    }

    /** Sets how many units renew next period (0 = cancel). Takes effect at the next renewal. */
    public function setRenewQuantity(Order $order, Entitlement $item, int $quantity): OrderEntitlement
    {
        $extra = OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $item->id)->firstOrFail();
        $max = $item->type === 'boolean' ? 1 : $extra->quantity;
        if ($quantity < 0 || $quantity > $max) {
            throw ValidationException::withMessages(['quantity' => __('message.extra_reduce_range', ['max' => $max])]);
        }

        $extra->renew_quantity = $quantity === $extra->quantity ? null : $quantity;
        $extra->save();

        return $extra;
    }

    /** At renewal, reductions and cancellations take effect. */
    public function applyPendingReductions(int $orderId): void
    {
        foreach (OrderEntitlement::where('order_id', $orderId)->whereNotNull('renew_quantity')->get() as $extra) {
            $extra->renew_quantity === 0
                ? $extra->delete()
                : $extra->update(['quantity' => $extra->renew_quantity, 'renew_quantity' => null]);
        }
    }

    /** What the order's extras add to one renewal, in the renewal's currency and billing period. */
    public function renewalCost(int $orderId, string $currency, ?Plan $plan): float
    {
        $periodId = $plan?->periodId();
        $total = 0.0;
        foreach ($this->billableExtras($orderId, $plan) as $extra) {
            $total += $this->extraUnitPrice($extra, $currency, $plan, $periodId) * $extra->renewingQuantity();
        }

        return round($total, 2);
    }

    /**
     * What the order's extras are worth for one full period of $plan: the plan-change price
     * compares old and new plan *with* their extras, so an upgrade pays for the extras' new term
     * and a downgrade credits their unused part. On/off extras the plan already includes are
     * worth nothing there (carryExtras drops them).
     */
    public function extrasValue(Order $order, string $currency, ?Plan $plan): float
    {
        $periodId = $plan?->periodId();
        $total = 0.0;
        foreach ($this->billableExtras($order->id, $plan) as $extra) {
            $total += $this->extraUnitPrice($extra, $currency, $plan, $periodId) * $extra->quantity;
        }

        return round($total, 2);
    }

    /**
     * The order's extras that still cost money on $plan. Not an archived feature (the license
     * no longer carries it), and not an on/off feature the plan itself already switches on.
     *
     * @return \Illuminate\Support\Collection<int, OrderEntitlement>
     */
    private function billableExtras(int $orderId, ?Plan $plan): \Illuminate\Support\Collection
    {
        $planValues = $plan ? resolve(EntitlementService::class)->forPlan((int) $plan->product, $plan->id) : [];

        return OrderEntitlement::with('entitlement.prices')->where('order_id', $orderId)->get()
            ->filter(fn (OrderEntitlement $extra): bool => $extra->entitlement->is_active
                && ! ($extra->entitlement->type === 'boolean' && ($planValues[$extra->entitlement->feature][$extra->entitlement->key] ?? false) === true))
            ->values();
    }

    /**
     * One unit of an owned extra for a period of $plan. The customer keeps their price for the same
     * currency and period; another period uses today's price for it, else their own. Another
     * currency must have a price: their old amount is in a different currency (₹800 is not $800),
     * so without one the renewal stops with a clear message instead of guessing.
     */
    private function extraUnitPrice(OrderEntitlement $extra, string $currency, ?Plan $plan, ?int $periodId): float
    {
        if ($extra->currency === $currency && $extra->period_id === $periodId) {
            return (float) $extra->unit_price;
        }
        $price = $extra->entitlement->priceIn($currency, $plan, 'renewal');
        if ($price === null && $extra->currency !== $currency) {
            throw new \RuntimeException(__('message.extra_no_price_in_currency', ['name' => $extra->entitlement->label, 'currency' => $currency]));
        }

        return $price ?? (float) $extra->unit_price;
    }

    /**
     * Moves extras to the order that replaces this one (plan upgrade or downgrade), except on/off
     * features the new plan already switches on.
     */
    public function carryExtras(Order $from, Order $to, EntitlementService $entitlements): void
    {
        $newPlanId = Subscription::where('order_id', $to->id)->value('plan_id');
        $newPlan = $newPlanId ? Plan::find((int) $newPlanId) : null;
        $planValues = $entitlements->forPlan((int) $to->product, $newPlan?->id);

        foreach (OrderEntitlement::with('entitlement.prices')->where('order_id', $from->id)->get() as $extra) {
            $item = $extra->entitlement;
            if ($item->type === 'boolean' && ($planValues[$item->feature][$item->key] ?? false) === true) {
                continue; // the new plan already includes it
            }

            // The new plan's renewal price applies from now on; without one, the old price is kept.
            $newPrice = $item->priceIn($extra->currency, $newPlan, 'renewal');
            OrderEntitlement::updateOrCreate(
                ['order_id' => $to->id, 'entitlement_id' => $item->id],
                $newPrice === null
                    ? $extra->only(['quantity', 'renew_quantity', 'unit_price', 'currency', 'period_id'])
                    : [...$extra->only(['quantity', 'renew_quantity', 'currency']), 'unit_price' => (string) $newPrice, 'period_id' => $newPlan?->periodId()],
            );
        }
    }

    /**
     * The order's sellable items with what it has bought, for the customer's order page.
     *
     * @return list<array<string, mixed>>
     */
    public function forOrder(Order $order): array
    {
        $bought = OrderEntitlement::where('order_id', $order->id)->get()->keyBy('entitlement_id');
        $subscription = Subscription::where('order_id', $order->id)->first();
        $currency = $this->currencyFor($order, $subscription);
        $plan = $subscription ? Plan::find($subscription->plan_id) : null;
        $periodId = $plan?->periodId();

        $rows = [];
        $items = Entitlement::with('prices')
            ->where(fn ($q) => $q->whereIn('id', $bought->keys())->orWhereHas('prices', fn ($p) => $p->where('plan_id', $plan?->id)))
            ->orderBy('sort')->orderBy('id')->get();
        foreach ($items as $item) {
            $owned = $bought->has($item->id) ? $bought[$item->id] : null;
            if (! $item->canBeSoldOn($plan) && ! $owned) {
                continue;
            }

            $rows[] = [
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'quantity' => $owned ? $owned->quantity : 0,
                'renew_quantity' => $owned ? $owned->renewingQuantity() : 0,
                'unit_price' => $owned && $owned->currency === $currency && $owned->period_id === $periodId ? (float) $owned->unit_price : $item->priceIn($currency, $plan, 'renewal'),
                'currency' => $currency,
                'can_buy' => $item->canBeSoldOn($plan) && $item->priceIn($currency, $plan) !== null && ! ($item->type === 'boolean' && $owned),
            ];
        }

        return $rows;
    }

    /** The order's active subscription, or why extras can't be bought for it. */
    private function sellableSubscription(Order $order, Entitlement $item): Subscription
    {
        $subscription = Subscription::where('order_id', $order->id)->first();

        $reason = match (true) {
            $order->order_status === 'Terminated' || ! $subscription => 'extra_order_inactive',
            ! $item->canBeSoldOn(Plan::find($subscription->plan_id)) => 'extra_not_for_sale',
            ! $subscription->termEndsAt() || $subscription->termEndsAt()->isPast() => 'extra_order_expired',
            CloudProducts::where('cloud_free_plan', $subscription->plan_id)->exists() => 'extra_free_trial',
            default => null,
        };

        if ($reason) {
            throw ValidationException::withMessages(['entitlement_id' => __("message.$reason")]);
        }

        return $subscription;
    }

    private function currencyFor(Order $order, ?Subscription $subscription): string
    {
        $plan = $subscription ? Plan::find($subscription->plan_id) : null;

        return (string) (userCurrencyAndPrice($order->client, $plan)['currency'] ?: 'USD');
    }

    /**
     * The customer's own price if they bought this before, otherwise today's price for the plan's period.
     *
     * @param  'purchase'|'renewal'  $kind
     */
    private function unitPrice(Order $order, Entitlement $item, string $currency, ?Plan $plan, string $kind = 'purchase'): float
    {
        $periodId = $plan?->periodId();
        $owned = OrderEntitlement::where('order_id', $order->id)->where('entitlement_id', $item->id)->first();
        if ($owned && $owned->currency === $currency && $owned->period_id === $periodId) {
            return (float) $owned->unit_price;
        }

        $price = $item->priceIn($currency, $plan, $kind);
        if ($price === null) {
            throw ValidationException::withMessages(['entitlement_id' => __('message.extra_no_price', ['currency' => $currency])]);
        }

        return $price;
    }
}
