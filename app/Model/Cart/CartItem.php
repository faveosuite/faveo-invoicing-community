<?php

namespace App\Model\Cart;

use App\Model\Payment\Plan;
use App\Model\Product\Entitlement;
use App\Model\Product\Product;
use App\Services\EntitlementSalesService;
use App\Services\EntitlementService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $cart_id
 * @property int $product_id
 * @property int|null $plan_id
 * @property int $quantity
 * @property array<int|string, int>|null $extras
 * @property string|null $domain
 * @property int|null $data_center_id
 * @property string $billing_cycle
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Cart $cart
 * @property-read Plan|null $plan
 * @property-read Product|null $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereBillingCycle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereDataCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id', 'product_id', 'plan_id',
        'quantity', 'extras', 'domain', 'data_center_id', 'billing_cycle',
    ];

    /**
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return ['extras' => 'array'];
    }

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * False once the product is deleted, or its plan or this currency's price is gone —
     * priceFor() would then charge 0, so such a line must leave the cart. A plan that includes
     * 0 agents also needs an Agents price in the currency, or every agent would be free.
     */
    public function isAvailable(string $currency): bool
    {
        return $this->product !== null
            && $this->plan !== null
            && (int) $this->plan->product === (int) $this->product_id
            && ($row = $this->plan->planPrice->firstWhere('currency', $currency)) !== null
            && (EntitlementService::includedAgents($this->plan) !== 0
                || EntitlementService::agents()->priceIn($currency, $this->plan) !== null);
    }

    public function priceFor(string $currency): float
    {
        $planPrice = $this->plan?->planPrice->firstWhere('currency', $currency);

        // The offer price is a discount off add_price, as the storefront shows it.
        return $planPrice ? resolve(EntitlementSalesService::class)->planPrice($planPrice, 'purchase') : 0.0;
    }

    /**
     * The product locks its license to a customer domain, so one must be
     * entered before checkout. Cloud products carry their own subdomain instead.
     */
    public function needsDomain(): bool
    {
        return (bool) $this->product?->require_domain && ! in_array($this->product_id, cloudPopupProducts());
    }

    /** Price of one unit of this line: the plan price plus the chosen extras (Agents among them). */
    public function unitPrice(string $currency): float
    {
        return $this->priceFor($currency) + array_sum(array_map(
            fn (array $extra): float => $extra['unit_price'] * $extra['quantity'],
            $this->chosenExtras($currency),
        ));
    }

    /**
     * Agents the license gets: what the plan includes plus the Agents extra. 0 for a flat or an
     * unlimited plan — the license code's 0000, which reads as unlimited.
     */
    public function totalAgents(string $currency): int
    {
        $included = EntitlementService::includedAgents($this->plan);
        if ($included === null) {
            return 0;
        }
        $extra = collect($this->chosenExtras($currency))->firstWhere('entitlement_id', EntitlementService::agents()->id);

        return $included + (int) ($extra['quantity'] ?? 0);
    }

    /** What the chosen extras add to this line (all units of the line). */
    public function extrasTotal(string $currency): float
    {
        return array_sum(array_map(
            fn (array $extra): float => $extra['unit_price'] * $extra['quantity'],
            $this->chosenExtras($currency),
        )) * $this->quantity;
    }

    /**
     * Extras the plan sells in the currency, with how many the customer picked. `included` is
     * what the plan already gives of a number it also sells more of (Storage: 10 included, buy
     * more); the customer starts there and can't go below it. `quantity` is always the extra
     * units on top, which is what's charged. null = not in the plan, a pure add-on. `min` is the
     * fewest extra units the line can have: 1 Agent on a plan that includes 0, else 0.
     *
     * `max` is the most extra units it takes: 1 for an on/off feature; Agents stop where the license
     * code's four digits do.
     *
     * @return list<array{entitlement_id: int, label: string, type: string, unit_price: float, renew_price: float, quantity: int, included: int|null, min: int, max: int, period_id: int|null}>
     */
    public function availableExtras(string $currency): array
    {
        $chosen = $this->extras ?? [];
        $periodId = $this->plan?->periodId();
        $rows = [];

        $items = Entitlement::with('prices')->whereHas('prices', fn ($q) => $q->where('plan_id', $this->plan_id))
            ->orderBy('sort')->orderBy('id')->get();
        $planValues = $this->plan?->entitlements()->pluck('plan_entitlements.value', 'entitlements.id') ?? collect();
        foreach ($items as $item) {
            $price = $item->priceIn($currency, $this->plan);
            $renewPrice = $item->priceIn($currency, $this->plan, 'renewal');
            if (! $item->canBeSoldOn($this->plan) || $price === null) {
                continue;
            }
            $included = $item->type === 'number' && $planValues->has($item->id) ? (int) $planValues[$item->id] : null;
            $min = $item->code === EntitlementService::AGENTS && $included === 0 ? 1 : 0;
            $rows[] = [
                'entitlement_id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'unit_price' => $price,
                'renew_price' => (float) $renewPrice,
                'quantity' => max($min, (int) ($chosen[$item->id] ?? 0)),
                'included' => $included,
                'min' => $min,
                'max' => match (true) {
                    $item->type === 'boolean' => 1,
                    $item->code === EntitlementService::AGENTS => 9999 - (int) $included,
                    default => 9999,
                },
                'period_id' => $periodId,
            ];
        }

        return $rows;
    }

    /**
     * Keeps only extras this line's product sells in the currency, with a valid quantity
     * (an on/off feature is 0 or 1, never below the extra's `min`). Agents stop where the license
     * code's four digits do. Merged into what the line already has; 0 removes one.
     *
     * @param  array<int|string, mixed>  $requested
     * @return array<int|string, int>
     */
    public function cleanExtras(array $requested, string $currency): array
    {
        $extras = $this->extras ?? [];

        foreach ($this->availableExtras($currency) as $extra) {
            $id = $extra['entitlement_id'];
            if (! array_key_exists($id, $requested)) {
                continue;
            }
            $qty = max($extra['min'], min($extra['max'], (int) $requested[$id]));
            if ($qty === 0) {
                unset($extras[$id]);
            } else {
                $extras[$id] = $qty;
            }
        }

        return $extras;
    }

    /**
     * The extras the customer picked that are still on sale in the currency: these are charged,
     * written to the invoice line, and given to the order once it's paid.
     *
     * @return list<array{entitlement_id: int, label: string, quantity: int, unit_price: float, renew_price: float, period_id: int|null}>
     */
    public function chosenExtras(string $currency): array
    {
        $rows = [];
        foreach ($this->availableExtras($currency) as $extra) {
            if ($extra['quantity'] > 0) {
                unset($extra['type'], $extra['included'], $extra['min'], $extra['max']); // display-only; the invoice keeps what's charged
                $rows[] = $extra;
            }
        }

        return $rows;
    }

    public function lineTotal(): float
    {
        return $this->unitPrice($this->cart?->currency ?? 'USD') * $this->quantity; // @phpstan-ignore nullCoalesce.expr
    }
}
