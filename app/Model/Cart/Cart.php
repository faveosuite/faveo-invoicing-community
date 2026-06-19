<?php

namespace App\Model\Cart;

use App\Model\Order\Invoice;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $coupon_code
 * @property float $coupon_discount
 * @property string $currency
 * @property int|null $invoice_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Invoice|null $invoice
 * @property-read Collection<int, CartItem> $items
 * @property-read int|null $items_count
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCouponCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCouponDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Cart whereUserId($value)
 *
 * @mixin \Eloquent
 */
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
