<?php

namespace App\Model\Cart;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Cart\Cart $cart
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

    public function lineTotal(): float
    {
        return $this->priceFor($this->cart?->currency ?? 'USD') * $this->quantity * $this->agents;
    }
}
