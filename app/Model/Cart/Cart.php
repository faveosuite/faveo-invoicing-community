<?php

namespace App\Model\Cart;

use App\Model\Order\Invoice;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

class Cart extends Model
{
    protected $table = 'carts';

    protected $fillable = ['user_id', 'coupon_code', 'coupon_discount', 'currency', 'invoice_id'];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The pending invoice generated for this cart at place-order time (if any).
     * @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function subtotal(): float
    {
        $currency = $this->currency ?? 'USD';

        return (float) $this->items->sum(
            fn (CartItem $item): float => $item->priceFor($currency) * $item->quantity * $item->agents
        );
    }

    public function total(): float
    {
        return max(0, $this->subtotal() - (float) $this->coupon_discount);
    }

    public function itemCount(): int
    {
        return (int) $this->items->sum('quantity');
    }

    #[Override]
    protected function casts(): array
    {
        return ['coupon_discount' => 'float'];
    }
}
