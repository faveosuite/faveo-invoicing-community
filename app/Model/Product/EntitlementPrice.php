<?php

declare(strict_types=1);

namespace App\Model\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Override;

/**
 * What one more unit of a feature costs on one plan (so for that plan's billing period), in one currency.
 *
 * @property int $id
 * @property int $entitlement_id
 * @property string $currency
 * @property int $plan_id
 * @property string $unit_price
 * @property string|null $renew_price
 * @property string|null $offer_percent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class EntitlementPrice extends Model
{
    protected $fillable = ['entitlement_id', 'plan_id', 'currency', 'unit_price', 'renew_price', 'offer_percent'];

    #[Override]
    protected function casts(): array
    {
        return ['entitlement_id' => 'integer', 'plan_id' => 'integer', 'unit_price' => 'decimal:4', 'renew_price' => 'decimal:4', 'offer_percent' => 'decimal:2'];
    }
}
