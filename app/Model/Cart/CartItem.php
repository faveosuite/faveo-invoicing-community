<?php

namespace App\Model\Cart;

use App\Model\Payment\Plan;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id', 'product_id', 'plan_id',
        'quantity', 'agents', 'domain', 'data_center_id', 'billing_cycle',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

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
