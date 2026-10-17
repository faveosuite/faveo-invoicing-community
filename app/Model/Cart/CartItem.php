<?php

namespace App\Model\Cart;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
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
        'quantity', 'agents', 'domain', 'data_center_id', 'billing_cycle',
    ];

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
        $price = $this->priceFor($currency);
        $planAgents = self::planAgents($this->plan, $currency);

        return $this->agents > 0 && $planAgents > 0
            ? $price / $planAgents * $this->agents
            : $price;
    }

    public function lineTotal(): float
    {
        return $this->unitPrice($this->cart?->currency ?? 'USD') * $this->quantity; // @phpstan-ignore nullCoalesce.expr
    }
}
