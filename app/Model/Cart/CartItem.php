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
 * @property int $agents
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CartItem whereAgents($value)
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
        'quantity', 'agents', 'extras', 'domain', 'data_center_id', 'billing_cycle',
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
     * priceFor() would then charge 0, so such a line must leave the cart.
     */
    public function isAvailable(string $currency): bool
    {
        return $this->product !== null
            && $this->plan !== null
            && (int) $this->plan->product === (int) $this->product_id
            && ($row = $this->plan->planPrice->firstWhere('currency', $currency)) !== null
            && resolve(EntitlementSalesService::class)->canPrice($row);
    }

    public function priceFor(string $currency): float
    {
        $planPrice = $this->plan?->planPrice->firstWhere('currency', $currency);
        if (! $planPrice) {
            return 0.0;
        }

        // Mirror the storefront pricing (StoreController::getProductPlans):
        // offer_price is a percentage discount off add_price. Charging add_price
        // here would bill the original price instead of the discounted one shown.
        $base = (float) ($planPrice->add_price ?? 0);
        $offer = (float) ($planPrice->offer_price ?? 0);

        return $offer > 0 ? $base * (1 - $offer / 100) : $base;
    }

    /**
     * The product locks its license to a customer domain, so one must be
     * entered before checkout. Cloud products carry their own subdomain instead.
     */
    public function needsDomain(): bool
    {
        return (bool) $this->product?->require_domain && ! in_array($this->product_id, cloudPopupProducts());
    }

    /**
     * How many agents the plan's price covers (plan_prices.no_of_agents). 0 means unlimited,
     * or simply not priced per agent — either way the plan price applies as-is.
     */
    public static function planAgents(?Plan $plan, string $currency): int
    {
        $prices = $plan?->planPrice;
        $row = $prices?->firstWhere('currency', $currency) ?? $prices?->first();

        return max(0, (int) ($row->no_of_agents ?? 0));
    }

    /**
     * Price of one unit of this line. The plan price is for the plan's own agent count;
     * a different count is charged pro rata — the same rule renewals use
     * (BaseRenewController::getCost) and the development branch's cart used. Fixed-agent
     * and unlimited lines always carry the plan's count, so they get the plan price.
     */
    public function unitPrice(string $currency): float
    {
        $row = $this->plan?->planPrice->firstWhere('currency', $currency);
        $planPrice = $row ? resolve(EntitlementSalesService::class)->planCost($row, (int) $this->agents, 'purchase') : 0.0;

        return $planPrice + array_sum(array_map(
            fn (array $extra): float => $extra['unit_price'] * $extra['quantity'],
            $this->chosenExtras($currency),
        ));
    }

    /** Agents the plan price covers: 0 when every agent is paid, null when not priced per agent (or unlimited). */
    public function includedAgents(string $currency): ?int
    {
        if (EntitlementService::paysForEveryAgent($this->plan)) {
            return 0;
        }
        $included = self::planAgents($this->plan, $currency);

        return $included > 0 ? $included : null;
    }

    /** One agent's price for this line's plan (purchase), or null when the plan isn't priced per agent. */
    public function agentPrice(string $currency): ?float
    {
        $row = $this->plan?->planPrice->firstWhere('currency', $currency);
        if (! $row || (int) $row->no_of_agents <= 0) {
            return null;
        }
        $sales = resolve(EntitlementSalesService::class);

        return $sales->agentUnitPrice($row, 'purchase') ?? $sales->planCost($row, (int) $row->no_of_agents, 'purchase') / (int) $row->no_of_agents;
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
     * Extras the plan sells in the currency, with how many the customer picked.
     *
     * @return list<array{entitlement_id: int, label: string, type: string, unit_price: float, renew_price: float, quantity: int, period_id: int|null}>
     */
    public function availableExtras(string $currency): array
    {
        $chosen = $this->extras ?? [];
        $periodId = $this->plan?->periodId();
        $rows = [];

        $items = Entitlement::with('prices')->whereHas('prices', fn ($q) => $q->where('plan_id', $this->plan_id))
            ->orderBy('sort')->orderBy('id')->get();
        foreach ($items as $item) {
            $price = $item->priceIn($currency, $this->plan);
            $renewPrice = $item->priceIn($currency, $this->plan, 'renewal');
            if (! $item->canBeSoldOn($this->plan) || $price === null) {
                continue;
            }
            $rows[] = [
                'entitlement_id' => $item->id,
                'label' => $item->label,
                'type' => $item->type,
                'unit_price' => $price,
                'renew_price' => (float) $renewPrice,
                'quantity' => (int) ($chosen[$item->id] ?? 0),
                'period_id' => $periodId,
            ];
        }

        return $rows;
    }

    /**
     * The extras the customer picked that are still on sale in the currency: these are charged,
     * written to the invoice line, and given to the order once it's paid.
     *
     * @return list<array{entitlement_id: int, label: string, quantity: int, unit_price: float, renew_price: float, period_id: int|null}>
     */
    public function chosenExtras(string $currency): array
    {
        if (empty($this->extras)) {
            return [];
        }

        $rows = [];
        foreach ($this->availableExtras($currency) as $extra) {
            if ($extra['quantity'] > 0) {
                unset($extra['type']);
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
