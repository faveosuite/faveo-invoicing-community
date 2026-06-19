<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;

/**
 * Postcode/city narrowing for a tax rate. location_type is 'postcode' or
 * 'city'; postcode codes may be wildcards/ranges (see TaxRateResolver).
 *
 * @property int $id
 * @property int $tax_rate_id
 * @property string $location_code
 * @property string $location_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Payment\TaxRate $taxRate
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereLocationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereLocationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereTaxRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TaxRateLocation whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TaxRateLocation extends BaseModel
{
    protected $table = 'tax_rate_locations';

    protected $fillable = ['tax_rate_id', 'location_code', 'location_type'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<TaxRate, $this>
     */
    public function taxRate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }
}
