<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Product\Entitlement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Override;

/**
 * An extra an order bought on top of its plan: `quantity` units at `unit_price` per billing
 * period. `renew_quantity` holds a reduction or cancellation that applies at the next renewal.
 *
 * @property int $id
 * @property int $order_id
 * @property int $entitlement_id
 * @property int $quantity
 * @property int|null $renew_quantity
 * @property string $unit_price
 * @property string $currency
 * @property int|null $period_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Entitlement $entitlement
 * @property-read Order $order
 *
 * @mixin \Eloquent
 */
class OrderEntitlement extends Model
{
    protected $fillable = ['order_id', 'entitlement_id', 'quantity', 'renew_quantity', 'unit_price', 'currency', 'period_id'];

    /**
     * @return BelongsTo<Entitlement, $this>
     */
    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(Entitlement::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** Units billed at the next renewal. */
    public function renewingQuantity(): int
    {
        return $this->renew_quantity ?? $this->quantity;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'entitlement_id' => 'integer',
            'quantity' => 'integer',
            'renew_quantity' => 'integer',
            'unit_price' => 'decimal:4',
            'period_id' => 'integer',
        ];
    }
}
