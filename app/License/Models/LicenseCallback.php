<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

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
