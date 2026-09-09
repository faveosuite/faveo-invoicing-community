<?php

namespace App\License\Models;

use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $license_id
 * @property int $product_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read License $license
 * @property-read Product $product
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin whereLicenseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePlugin whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LicensePlugin extends Model
{
    protected $table = 'license_plugins';

    protected $fillable = [
        'license_id',
        'product_id',
    ];

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
