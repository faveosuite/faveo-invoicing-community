<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $product_id
 * @property int|null $user_id
 * @property string $license_code
 * @property string|null $callback_ip
 * @property string|null $callback_domain
 * @property \Illuminate\Support\Carbon $callback_date_time
 * @property int|null $callback_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\License\Models\License|null $license
 * @property-read Product|null $product
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereCallbackDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereCallbackDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereCallbackIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereCallbackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereLicenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseCallback whereUserId($value)
 *
 * @mixin \Eloquent
 */
class LicenseCallback extends Model
{
    protected $table = 'license_callbacks';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'callback_ip',
        'callback_domain',
        'callback_date_time',
        'callback_status',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<License, $this>
     */
    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_code', 'license_code');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'callback_date_time' => 'datetime',
            'callback_status' => 'integer',
            'product_id' => 'integer',
        ];
    }
}
