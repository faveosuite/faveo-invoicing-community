<?php

namespace App\Http\Controllers\Front\Cart;

use App\Http\Controllers\Common\SettingsController;
use App\Model\Cart\Cart;
use App\Model\Cart\CartItem;
use App\Model\Common\StatusSetting;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\InvoiceTaxLine;
use App\Model\Payment\Plan;
use App\Model\Payment\Promotion;
use App\Model\Payment\TaxOption;
use App\Services\Payment\InvoicePaymentService;
use App\Services\Payment\ProcessingFee;
use App\Services\Tax\TaxService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Throwable;

class CartService
{
    public function __construct(
        private readonly GuestCart $guest,
        private readonly InvoicePaymentService $invoices,
    ) {
    }

    // --- Cart resolution ---

    public function resolveCart(Request $request): Cart
    {
        if ($user = $request->user()) {
            $cart = $this->dbCart($user)->load('items.product', 'items.plan.planPrice');
            $cart->items->each(fn ($item) => $item->setRelation('cart', $cart));

            return $cart;
        }

        return $this->guest->toCart();
    }

    // --- Item operations ---

    /**
     * @param  array<mixed>  $data
     */
    public function addItem(Request $request, array $data): void
    {
        if ($user = $request->user()) {
            $this->addToDbCart($this->dbCart($user), $data);

            return;
        }

        $this->guest->add($data, $this->resolveGuestCurrency($request));
    }

    private function resolveGuestCurrency(Request $request): string
    {
        $ip = $request->ip();
        $iso = cache()->remember('user_location_'.$ip, 60, fn () => getLocation($ip)['iso_code'] ?? null);

        return getCurrencyForClient($iso ? (string) findCountryByGeoip($iso) : '');
    }

    /**
     * @param  array<mixed>  $data
     */
    public function updateItem(Request $request, int|string $itemId, array $data): void
    {
        if ($request->user()) {
            $item = CartItem::findOrFail($itemId);
            $item->update(array_filter([
                'quantity' => $data['quantity'] ?? null,
                'agents' => $data['agents'] ?? null,
                'domain' => $data['domain'] ?? null,
            ], fn ($v): bool => $v !== null));
            $this->recalculateCoupon($item->cart);

            return;
        }

        $this->guest->update((int) $itemId, $data);
    }

    public function removeItem(Request $request, int|string $itemId): void
    {
        if ($request->user()) {
            $item = CartItem::findOrFail($itemId);
            $cart = $item->cart;
            $item->delete();
            $this->recalculateCoupon($cart);

            return;
        }

        $this->guest->remove((int) $itemId);
    }

    public function clear(Request $request): void
    {
        if ($user = $request->user()) {
            $cart = $this->dbCart($user);
            $cart->items()->delete();
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0, 'invoice_id' => null]);

            return;
        }

        $this->guest->clear();
    }

    public function ownsItem(Request $request, int|string $itemId): bool
    {
        if ($user = $request->user()) {
            return CartItem::where('id', $itemId)
                ->whereHas('cart', fn ($q) => $q->where('user_id', $user->getAuthIdentifier()))
                ->exists();
        }

        return $this->guest->has((int) $itemId);
    }

    // --- Coupons (auth only) ---

    public function applyCoupon(Request $request, string $code): void
    {
        $cart = $this->dbCart($request->user()); // @phpstan-ignore argument.type
        $promo = $this->validatedPromotion($code);

        $cart->update([
            'coupon_code' => $code,
            'coupon_discount' => $this->discountFor($cart->subtotal(), $promo),
        ]);
    }

    public function removeCoupon(Request $request): void
    {
        $this->dbCart($request->user())->update(['coupon_code' => null, 'coupon_discount' => 0]); // @phpstan-ignore argument.type
    }

    // --- Checkout summary ---

    /**
     * Money summary for the checkout page. Uses the exact same per-line tax and
     * `rounding()` rules as invoice creation so the "Total" shown here always
     * equals the invoice's grand_total (and therefore the pay page's amount due).
     *
     * @return array<mixed>
     */
    public function checkoutExtras(Cart $cart, Authenticatable $user): array
    {
        $summary = $this->summary($cart, $user);
        $currency = $cart->currency ?? 'USD';

        $pricesIncludeTax = (int) TaxOption::find(1)?->inclusive === 1;
        $subtotalExTax = $pricesIncludeTax
            ? currencyFormat($summary['subtotal'] - $summary['tax_total'], $currency, includeSymbol: false)
            : currencyFormat($summary['subtotal'], $currency, includeSymbol: false);

        return [
            'taxes' => array_map(
                fn (array $t): array => array_merge($t, ['amount' => currencyFormat($t['amount'], $currency, includeSymbol: false)]),
                $summary['taxes']
            ),
            'tax_total' => currencyFormat($summary['tax_total'], $currency, includeSymbol: false),
            'subtotal_ex_tax' => $subtotalExTax,
            'prices_include_tax' => $pricesIncludeTax,
            'tax_label' => collect($summary['taxes'])->pluck('label')->unique()->implode(' + '),
            'gateways' => $gateways = $this->activeGateways($currency),
            'grand_total' => currencyFormat($summary['grand_total'], $currency, includeSymbol: false),
            'available_credit' => currencyFormat($this->invoices->availableCredit((int) $user->getAuthIdentifier()), $currency, includeSymbol: false),
            'auto_renew_gateways' => $this->autoRenewalGateways($gateways),
        ];
    }

    /**
     * Which of the active gateways can actually run auto-renewal right now —
     * mirrors {@see \App\Http\Controllers\Front\ClientController::autoRenewalGateways()},
     * the same check the existing post-purchase "Enable auto-renewal" tab
     * uses. A list, not a single yes/no: the checkout modal must only offer
     * auto-renew when it matches the gateway the customer actually selected,
     * not merely "some gateway supports it." Not product-specific — every
     * order gets a Subscription row regardless of product.
     *
     * @param  array<int, array{name: string, processing_fee: float|null}>  $gateways
     * @return array<int, string>
     */
    private function autoRenewalGateways(array $gateways): array
    {
        $active = array_map(fn (array $g): string => strtolower($g['name']), $gateways);

        return array_values(array_filter(
            ['Stripe', 'Razorpay'],
            fn (string $gateway): bool => StatusSetting::autoRenewalEnabledFor($gateway) && in_array(strtolower($gateway), $active, true)
        ));
    }

    // --- Place order (invoice creation / reuse) ---

    /**
     * Turn the cart into a payable invoice and return it.
     *
     * A cart is linked to its invoice (carts.invoice_id). If that invoice is
     * still pending and unpaid we rebuild it in place — so editing the cart and
     * checking out again updates the same invoice instead of spawning a new one.
     * Only once payment succeeds (PostPaymentHandle::processPaymentSuccess) is
     * the cart emptied and the link cleared. Payment always charges the invoice,
     * never the cart.
     */
    public function placeOrder(Cart $cart, Authenticatable $user, bool $autoRenewOptIn = false, ?string $gateway = null): Invoice
    {
        $cart->loadMissing('items.plan.planPrice', 'items.product');
        $this->recalculateCoupon($cart);   // drop a coupon that expired since it was applied
        $cart->refresh()->loadMissing('items.plan.planPrice', 'items.product');

        return DB::transaction(function () use ($cart, $user, $autoRenewOptIn, $gateway) {
            $summary = $this->summary($cart, $user);
            $invoice = $this->reusablePendingInvoice($cart);

            $cloudItem = $cart->items->first(
                fn ($item): bool => $item->domain && in_array($item->product_id, cloudPopupProducts())
            );

            $attributes = [
                'user_id' => $user->getAuthIdentifier(),
                'date' => Date::now(),
                'grand_total' => $summary['grand_total'],
                'status' => 'pending',
                'currency' => $cart->currency ?? 'USD',
                'coupon_code' => $cart->coupon_code,
                'discount' => $summary['discount'],
                'discount_mode' => 'coupon',
                'is_renewed' => 0,
                'cloud_domain' => $cloudItem?->domain ?: null,
                // Last write wins: re-checkout after changing the modal choice
                // overwrites this on the same reused pending invoice. Checked
                // against the specific selected gateway, not "any gateway" —
                // auto-renew must only apply if it's actually available for
                // however this invoice ends up getting paid.
                'metadata' => $autoRenewOptIn && $gateway && in_array($gateway, $this->autoRenewalGateways($this->activeGateways($cart->currency ?? 'USD')), true)
                    ? ['auto_renew_opt_in' => true]
                    : null,
            ];

            if ($invoice instanceof Invoice) {
                $invoice->update($attributes);
                $invoice->invoiceItem()->delete();
                InvoiceTaxLine::where('invoice_id', $invoice->id)->delete();
            } else {
                $invoice = Invoice::create($attributes + ['number' => random_int(11111111, 99999999)]);
            }

            foreach ($summary['items'] as $item) {
                $breakdown = $item['tax_breakdown'] ?? [];
                unset($item['tax_breakdown']); // not an invoice_items column

                $invoiceItem = InvoiceItem::create(['invoice_id' => $invoice->id] + $item);

                foreach ($breakdown as $taxLine) {
                    InvoiceTaxLine::create([
                        'invoice_id' => $invoice->id,
                        'invoice_item_id' => $invoiceItem->id,
                        'tax_rate_id' => $taxLine['tax_rate_id'],
                        'label' => $taxLine['label'],
                        'rate' => $taxLine['rate'],
                        'compound' => $taxLine['compound'],
                        'amount' => $taxLine['amount'],
                    ]);
                }
            }

            $cart->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    /**
     * The cart's linked invoice, but only if it is safe to rebuild — i.e. still
     * pending and with no payment recorded against it. A paid or part-paid
     * invoice is left untouched and a fresh one is created instead.
     */
    private function reusablePendingInvoice(Cart $cart): ?Invoice
    {
        $invoice = $cart->invoice_id ? Invoice::find($cart->invoice_id) : null;

        if ($invoice && strtolower((string) $invoice->status) === 'pending' && (float) $invoice->payment()->sum('amount') === 0.0) {
            return $invoice;
        }

        return null;
    }

    // --- Merge guest cart on login ---

    public function mergeGuestCart(Authenticatable $user): void
    {
        if ($this->guest->isEmpty()) {
            $this->guest->clear();

            return;
        }

        $cart = $this->dbCart($user);

        foreach ($this->guest->all() as $data) {
            $this->addToDbCart($cart, $data);
        }

        $this->guest->clear();
    }

    // --- Private helpers ---

    private function dbCart(Authenticatable $user): Cart
    {
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->getAuthIdentifier()],
            ['currency' => getCurrencyForClient($user->country)]
        );

        if (empty($cart->currency)) {
            $cart->update(['currency' => getCurrencyForClient($user->country)]);
        }

        return $cart;
    }

    /**
     * @param  array<mixed>  $data
     */
    private function addToDbCart(Cart $cart, array $data): void
    {
        $planId = $data['plan_id'] ?? null;

        if (! $planId) {
            $planId = Plan::where('product', $data['product_id'])
                ->where('status', 1)
                ->value('id');

            if (! $planId) {
                abort(422, 'This product has no active plan and cannot be added to the cart.');
            }
        }

        $existing = $cart->items()
            ->where('product_id', $data['product_id'])
            ->where('plan_id', $planId)
            ->where('billing_cycle', $data['billing_cycle'] ?? 'monthly')
            ->first();

        if ($existing) {
            $existing->increment('quantity', $data['quantity'] ?? 1);
        } else {
            $cart->items()->create([
                'product_id' => $data['product_id'],
                'plan_id' => $planId,
                'quantity' => $data['quantity'] ?? 1,
                'agents' => $data['agents'] ?? 1,
                'domain' => $data['domain'] ?? null,
                'data_center_id' => $data['data_center_id'] ?? null,
                'billing_cycle' => $data['billing_cycle'] ?? 'monthly',
            ]);
        }

        $this->recalculateCoupon($cart);
    }

    /**
     * Single source of truth for cart money: per-line invoice items, grouped
     * taxes for display, and the totals. Both the checkout summary and invoice
     * creation read from here so the numbers can never drift apart.
     *
     * @return array{items: array<mixed>, subtotal: float, discount: float, taxes: array<mixed>, tax_total: float, grand_total: float}
     */
    private function summary(Cart $cart, Authenticatable $user): array
    {
        $currency = $cart->currency ?? 'USD';
        $items = [];
        $grouped = [];
        $taxTotal = 0.0;

        foreach ($this->lineTaxes($cart, $user) as $tax) {
            $line = $tax['line'];
            $lineTotal = $line->priceFor($currency) * $line->quantity * $line->agents;

            $items[] = [
                'product_name' => $line->product?->name,
                'product_id' => $line->product_id,
                'regular_price' => $line->priceFor($currency),
                'quantity' => $line->quantity,
                'tax_name' => $tax['name'],
                'tax_percentage' => $tax['percent_label'],
                'tax_rate_id' => $tax['tax_rate_id'],
                'tax_breakdown' => $tax['breakdown'], // stripped before persist; drives invoice_tax_lines
                'subtotal' => $lineTotal,
                'domain' => $line->domain ?: '',
                'plan_id' => $line->plan_id ?? 0,
                'agents' => $line->agents,
            ];

            // Group taxes per rate (not the combined line label) so multiple
            // taxes show as separate lines — consistent with the invoice view.
            foreach ($tax['breakdown'] as $rateLine) {
                if ($rateLine['amount'] <= 0) {
                    continue;
                }

                $key = $rateLine['label'];
                $grouped[$key]['label'] = $rateLine['label'];
                $grouped[$key]['rate'] = $rateLine['rate'];
                $grouped[$key]['amount'] = ($grouped[$key]['amount'] ?? 0) + $rateLine['amount'];
                $taxTotal += $rateLine['amount'];
            }
        }

        $taxes = [];
        foreach ($grouped as $g) {
            $taxes[] = ['label' => $g['label'], 'rate' => $g['rate'], 'amount' => $g['amount']];
        }

        $subtotal = $cart->subtotal();
        $discount = (float) $cart->coupon_discount;

        // When prices are entered inclusive of tax, the tax is already inside
        // the subtotal — show it for information but do not add it again.
        $pricesIncludeTax = (int) TaxOption::find(1)?->inclusive === 1;
        $payable = $pricesIncludeTax
            ? max(0, $subtotal - $discount)
            : max(0, $subtotal - $discount + $taxTotal);

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'taxes' => $taxes,
            'tax_total' => $taxTotal,
            'grand_total' => rounding($payable, $cart->currency ?? 'USD'),
        ];
    }

    /**
     * Resolve the tax for every cart line once, via the generic tax engine.
     * Returns one entry per line so callers can group them (display), attach
     * them per-item (invoice) or persist the per-rate breakdown.
     *
     * @return array<int, array{line: CartItem, name: string, percent: float, percent_label: string, amount: float, tax_rate_id: ?int, breakdown: array<mixed>}>
     */
    private function lineTaxes(Cart $cart, Authenticatable $user): array
    {
        $currency = $cart->currency ?? 'USD';
        $tax = resolve(TaxService::class);

        return $cart->items->map(function (CartItem $line) use ($user, $currency, $tax): array {
            $lineTotal = $line->priceFor($currency) * $line->quantity * $line->agents;
            $result = $tax->calculate($lineTotal, (int) $line->product_id, $user);

            // A single applied rate can be referenced directly on the item;
            // multi-rate/compound lines rely on the persisted breakdown.
            $rateId = ($result['applicable'] && count($result['lines']) === 1)
                ? $result['lines'][0]['tax_rate_id']
                : null;

            return [
                'line' => $line,
                'name' => $result['applicable'] ? $result['name'] : '',
                'percent' => $result['percent'],
                'percent_label' => $result['applicable'] ? $this->percentLabel($result['percent']) : '',
                'amount' => $result['total'],
                'tax_rate_id' => $rateId,
                'breakdown' => $result['lines'],
            ];
        })->all();
    }

    private function percentLabel(float $percent): string
    {
        return rtrim(rtrim(number_format($percent, 4, '.', ''), '0'), '.').'%';
    }

    /**
     * @return array<mixed>
     */
    private function activeGateways(string $currency): array
    {
        try {
            $names = SettingsController::checkPaymentGateway($currency);
            if (! is_array($names)) {
                return [];
            }

            return array_map(fn ($name): array => ['name' => $name, 'processing_fee' => ProcessingFee::percent($name) ?: null], $names);
        } catch (Throwable) {
            return [];
        }
    }

    private function recalculateCoupon(Cart $cart): void
    {
        $cart = $cart->fresh(['items']);

        if (empty($cart->coupon_code)) {
            return;
        }

        // An emptied cart drops its coupon outright — otherwise the code lingers
        // on the row and silently re-applies the moment an item is added back.
        if ($cart->items->isEmpty()) {
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0]);

            return;
        }

        try {
            $promo = $this->validatedPromotion($cart->coupon_code);
        } catch (Exception) {
            $cart->update(['coupon_code' => null, 'coupon_discount' => 0]);

            return;
        }

        $cart->update(['coupon_discount' => $this->discountFor($cart->subtotal(), $promo)]);
    }

    private function validatedPromotion(string $code): Promotion
    {
        $promo = Promotion::where('code', $code)->first();

        if (! $promo || ! $this->withinValidityWindow($promo)) {
            throw new Exception(__('message.invalid_coupon_code'));
        }

        if (Invoice::where('coupon_code', $code)->count() >= $promo->uses) {
            throw new Exception(__('message.usage-of-code-completed'));
        }

        return $promo;
    }

    private function discountFor(float $subtotal, Promotion $promo): float
    {
        $raw = (string) $promo->value;
        $isPercentage = str_contains($raw, '%') || (int) $promo->type === 1;
        $numeric = (float) preg_replace('/[^0-9.]/', '', $raw);

        return $isPercentage
            ? round($subtotal * ($numeric / 100), 2)
            : min($numeric, $subtotal);
    }

    private function withinValidityWindow(Promotion $promo): bool
    {
        $now = Date::now();

        if ($this->hasDateBound($promo->start) && $now->lt(Date::parse($promo->start))) {
            return false;
        }

        return ! ($this->hasDateBound($promo->expiry) && $now->gt(Date::parse($promo->expiry)));
    }

    /**
     * Whether a promotion date is a real bound. null/empty and the legacy
     * "0000-00-00" sentinel both mean "no limit" (a coupon with neither bound is
     * always valid) — Carbon would otherwise parse "0000-00-00" as year -1 and
     * read every such coupon as expired.
     */
    private function hasDateBound(mixed $value): bool
    {
        return ! empty($value) && ! str_starts_with((string) $value, '0000-00-00');
    }
}
