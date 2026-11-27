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
use Logger;

/**
 * Selling extras on top of a plan: more units of a number (storage, policies) or an on/off
 * feature. Bought extras are kept in order_entitlements at the price paid, added to what the
 * license grants (EntitlementService), and billed at every renewal (renewalCost()).
 *
 *  - Buying applies now; the customer pays for the days left in the current period.
 *  - Reducing or cancelling applies at the next renewal; nothing is refunded.
 *
 * The purchase invoice is deliberately NOT linked to the order through order_invoice_relations
 * (same as plan-change invoices): renewals and the order's invoice history read the newest
 * linked invoice line, and an extras line must not become that line.
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
            $item->code === EntitlementService::AGENTS && $quantity > $this->agentsRoom($order) => 'extra_agents_max',
            default => null,
        };
        if ($reason) {
            throw ValidationException::withMessages(['quantity' => __("message.$reason", ['max' => max(0, $this->agentsRoom($order))])]);
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
     * The plan's own price, for the agents it includes: renew_price, or add_price less offer_price %.
     * More agents are an Agents extra, priced on top like any extra.
     *
     * @param  'purchase'|'renewal'  $kind
     */
    public function planPrice(PlanPrice $planPrice, string $kind): float
    {
        return $kind === 'renewal'
            ? (float) $planPrice->renew_price
            : (float) $planPrice->add_price * (1 - (float) ($planPrice->offer_price ?? 0) / 100);
    }

    /** How many more agents the license code's four digits can hold (10000 would read as 0000, unlimited). */
    private function agentsRoom(Order $order): int
    {
        return 9999 - resolve(AgentCountService::class)->total($order);
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

            if (Entitlement::whereKey($extra->entitlement_id)->value('code') === EntitlementService::AGENTS) {
                $this->writeAgentCount($extra->order_id);
            }
        });
    }

    /**
     * After commit, puts the order's new agent count in its license (and cloud tenant). The extra
     * is already saved and paid for: a failed write is logged for an admin to fix, not undone.
     */
    private function writeAgentCount(int $orderId): void
    {
        DB::afterCommit(function () use ($orderId): void {
            $order = Order::find($orderId);
            if (! $order || ! $order->serial_key) {
                return; // no license to carry the count
            }
            $agents = resolve(AgentCountService::class);
            $total = $agents->total($order);
            try {
                // 0 would be written as 0000, which means unlimited: never the result of a sale or reduction.
                if ($total < 1) {
                    throw new \RuntimeException('agent count would be 0 (unlimited)');
                }
                $agents->write($order, $total);
            } catch (\Throwable $throwable) {
                Logger::exception(new \Exception(sprintf('Agents changed on order %d but its license was not updated: %s', $orderId, $throwable->getMessage()), 0, $throwable));
            }
        });
    }

    /**
     * A paid invoice that can't be applied (a stale extras invoice, or an old-style agent change):
     * its payment goes to the customer's credit.
     *
     * @param  array<string, mixed>  $meta
     */
    public function creditInstead(Invoice $invoice, array $meta, string $reason): void
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
        return $this->openInvoiceNumber($order, $item) !== null;
    }

    /** The number of an unpaid invoice already raised for this extra on this order, or null. */
    public function openInvoiceNumber(Order $order, Entitlement $item): ?string
    {
        $number = Invoice::where('status', '!=', 'success')
            ->where('metadata->type', self::INVOICE_TYPE)
            ->where('metadata->order_id', $order->id)
            ->where('metadata->entitlement_id', $item->id)
            ->value('number');

        return $number === null ? null : (string) $number;
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
        $min = match (true) {
            $extra->quantity < 0 => $extra->quantity, // a "fewer" extra (seeded): customers can't take more off
            // A plan including no agents keeps one: the license needs a count (0000 is unlimited).
            EntitlementService::isAgents($item) && EntitlementService::includedAgents(Plan::find((int) Subscription::where('order_id', $order->id)->value('plan_id'))) === 0 => 1,
            default => 0,
        };
        if ($quantity < $min || $quantity > $max) {
            throw ValidationException::withMessages(['quantity' => __('message.extra_reduce_range', ['min' => $min, 'max' => $max])]);
        }

        $extra->renew_quantity = $quantity === $extra->quantity ? null : $quantity;
        $extra->save();

        return $extra;
    }

    /** At renewal, reductions and cancellations take effect. */
    public function applyPendingReductions(int $orderId): void
    {
        $agentsId = EntitlementService::agents()->id;
        $agentsChanged = false;
        foreach (OrderEntitlement::where('order_id', $orderId)->whereNotNull('renew_quantity')->get() as $extra) {
            $extra->renew_quantity === 0
                ? $extra->delete()
                : $extra->update(['quantity' => $extra->renew_quantity, 'renew_quantity' => null]);
            $agentsChanged = $agentsChanged || $extra->entitlement_id === $agentsId;
        }

        if ($agentsChanged) {
            $this->writeAgentCount($orderId);
        }
    }

    /** What the order's extras add to one renewal, in the renewal's currency and billing period. */
    public function renewalCost(int $orderId, string $currency, ?Plan $plan): float
    {
        return round(array_sum(array_map(
            fn (array $extra): float => $extra['unit_price'] * $extra['quantity'],
            $this->renewalExtras($orderId, $currency, $plan),
        )), 2);
    }

    /**
     * The extras one renewal bills, as the renewal invoice line lists them. A negative extra (less
     * than the plan includes) reads "Fewer <feature> −n" and takes its price off.
     *
     * @return list<array{entitlement_id: int, label: string, quantity: int, unit_price: float}>
     */
    public function renewalExtras(int $orderId, string $currency, ?Plan $plan): array
    {
        $periodId = $plan?->periodId();
        $rows = [];
        foreach ($this->billableExtras($orderId, $plan) as $extra) {
            $quantity = $extra->renewingQuantity();
            if ($quantity === 0) {
                continue;
            }
            $rows[] = [
                'entitlement_id' => $extra->entitlement_id,
                'label' => $quantity < 0
                    ? __('message.extra_fewer', ['label' => $extra->entitlement->label, 'count' => -$quantity])
                    : $extra->entitlement->label,
                'quantity' => $quantity,
                'unit_price' => $this->extraUnitPrice($extra, $currency, $plan, $periodId),
            ];
        }

        return $rows;
    }

    /**
     * What the order's extras are worth for one full period of its own $plan: the plan-change
     * price compares old and new plan *with* their extras, so a downgrade credits only what was
     * actually paid. On/off extras the plan already includes are worth nothing.
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
     * What the order's extras will be worth for one full period of $to, the plan it moves to: the
     * number extras it carries there ({@see carriedNumbers()}) at $to's purchase price, plus its
     * on/off extras $to doesn't include.
     */
    public function movedExtrasValue(Order $from, Plan $to, string $currency): float
    {
        $periodId = $to->periodId();
        $total = 0.0;
        foreach ($this->billableExtras($from->id, $to)->where('entitlement.type', 'boolean') as $extra) {
            $total += $this->extraUnitPrice($extra, $currency, $to, $periodId) * $extra->quantity;
        }
        foreach ($this->carriedNumbers($from, $to, $currency) as $row) {
            if ($row['sold']) {
                $total += (float) $row['item']->priceIn($currency, $to) * $row['quantity'];
            }
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
                && ! ($extra->entitlement->type === 'boolean' && ($planValues[$extra->entitlement->code] ?? false) === true))
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
     * Moves extras to the order that replaces this one (plan upgrade or downgrade). Number extras
     * follow {@see carriedNumbers()}; one the new plan doesn't sell is dropped and logged. On/off
     * extras move unless the new plan already switches them on.
     */
    public function carryExtras(Order $from, Order $to, EntitlementService $entitlements): void
    {
        $newPlanId = Subscription::where('order_id', $to->id)->value('plan_id');
        $newPlan = $newPlanId ? Plan::find((int) $newPlanId) : null;
        $planValues = $entitlements->forPlan((int) $to->product, $newPlan?->id);

        foreach (OrderEntitlement::with('entitlement.prices')->where('order_id', $from->id)->get() as $extra) {
            $item = $extra->entitlement;
            if ($item->type !== 'boolean' || ($planValues[$item->code] ?? false) === true) {
                continue; // numbers below; an on/off feature the new plan already includes
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

        $currency = $this->currencyFor($to, Subscription::where('order_id', $to->id)->first());
        foreach ($this->carriedNumbers($from, $newPlan, $currency) as $row) {
            if (! $row['sold']) {
                if ($row['bought'] > 0) { // paid units lost
                    Logger::exception(new \RuntimeException(sprintf('Plan change from order %d to order %d dropped %d %s: the new plan does not sell it in %s',
                        $from->id, $to->id, $row['bought'], $row['item']->label, $currency)));
                }

                continue;
            }
            OrderEntitlement::updateOrCreate(
                ['order_id' => $to->id, 'entitlement_id' => $row['item']->id],
                ['quantity' => $row['quantity'], 'renew_quantity' => $row['renew_quantity'] === $row['quantity'] ? null : $row['renew_quantity'],
                    'unit_price' => (string) $row['item']->priceIn($currency, $newPlan, 'renewal'), 'currency' => $currency, 'period_id' => $newPlan?->periodId()],
            );
        }
    }

    /**
     * The number extras the order keeps when it moves to $to: what the customer bought, as is. The
     * old plan's own amount doesn't move (a 100 GB plan moved to a 50 GB plan gets 50 GB), and a
     * "fewer" (negative) extra is dropped: the new plan's own price applies. Nothing when $to
     * includes the feature without limit. `sold` = $to sells it in $currency, otherwise it can't be
     * billed there and is dropped. Agents: a plan including none keeps at least one (the license
     * needs a count), and an order an admin gave unlimited agents carries none (its override
     * carries over instead).
     *
     * @return list<array{item: Entitlement, quantity: int, renew_quantity: int, sold: bool, bought: int}>
     */
    public function carriedNumbers(Order $from, ?Plan $to, string $currency): array
    {
        $new = $to ? $to->entitlements()->pluck('plan_entitlements.value', 'entitlements.id') : collect();
        $agentCount = resolve(AgentCountService::class);
        $agentsId = EntitlementService::agents()->id;
        $agentsFloor = $new->has($agentsId) && $new[$agentsId] === '0' && ! $agentCount->hasOverride($from);

        $extras = OrderEntitlement::where('order_id', $from->id)->where('quantity', '>', 0)->get()->keyBy('entitlement_id');
        $ids = $extras->keys()->when($agentsFloor, fn ($ids) => $ids->push($agentsId));
        $rows = [];
        foreach (Entitlement::with('prices')->where('type', 'number')->whereIn('id', $ids->unique())->orderBy('sort')->orderBy('id')->get() as $item) {
            $isAgents = $item->id === $agentsId;
            if (($new->has($item->id) && $new[$item->id] === null) || ($isAgents && $agentCount->unlimited($from))) {
                continue; // unlimited on the new plan, or by the admin's override: nothing to pay for
            }
            $extra = $extras->get($item->id);
            $floor = $isAgents && $agentsFloor ? 1 : 0;
            $rows[] = ['item' => $item, 'quantity' => max($floor, $extra->quantity ?? 0), 'renew_quantity' => max($floor, $extra?->renewingQuantity() ?? 0),
                'sold' => $item->canBeSoldOn($to) && $item->priceIn($currency, $to, 'renewal') !== null, 'bought' => $extra->quantity ?? 0];
        }

        return $rows;
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

            // The same rules as buying it, so the page never offers what the purchase then refuses.
            $why = match (true) {
                $item->type === 'boolean' && $owned !== null => 'extra_already_owned',
                default => $this->whyNotSellable($order, $item) ?? ($item->priceIn($currency, $plan) === null ? 'extra_no_price' : null),
            };
            $rows[] = [
                'id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'quantity' => $owned ? $owned->quantity : 0,
                'renew_quantity' => $owned ? $owned->renewingQuantity() : 0,
                // What it costs each billing period (the list says so); buying today's share is quoted on Add.
                'unit_price' => $owned && $owned->currency === $currency && $owned->period_id === $periodId ? (float) $owned->unit_price : $item->priceIn($currency, $plan, 'renewal'),
                'currency' => $currency,
                'can_buy' => $why === null,
                // Shown next to an owned add-on that can't be bought more of (not for one already owned on/off).
                'reason' => $why === null || $why === 'extra_already_owned' ? null : __("message.$why", ['currency' => $currency]),
            ];
        }

        return $rows;
    }

    /** The order's active subscription, or why extras can't be bought for it. */
    private function sellableSubscription(Order $order, Entitlement $item): Subscription
    {
        $reason = $this->whyNotSellable($order, $item);
        if ($reason) {
            throw ValidationException::withMessages(['entitlement_id' => __("message.$reason")]);
        }

        return Subscription::where('order_id', $order->id)->firstOrFail();
    }

    /** Why this extra can't be bought for the order right now (a message key), or null when it can. */
    private function whyNotSellable(Order $order, Entitlement $item): ?string
    {
        $subscription = Subscription::where('order_id', $order->id)->first();

        return match (true) {
            $order->order_status === 'Terminated' || ! $subscription => 'extra_order_inactive',
            ! $item->canBeSoldOn(Plan::find($subscription->plan_id)) => 'extra_not_for_sale',
            // Unlimited by override, or on the license itself: bought agents would only lower it.
            EntitlementService::isAgents($item) && (resolve(AgentCountService::class)->unlimited($order) || resolve(AgentCountService::class)->licenseUnlimited($order)) => 'extra_agents_unlimited',
            // An admin's count replaces the order's: bought agents would change nothing.
            EntitlementService::isAgents($item) && resolve(AgentCountService::class)->hasOverride($order) => 'extra_agents_overridden',
            ! $subscription->termEndsAt() || $subscription->termEndsAt()->isPast() => 'extra_order_expired',
            CloudProducts::where('cloud_free_plan', $subscription->plan_id)->exists() => 'extra_free_trial',
            default => null,
        };
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
