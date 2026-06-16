<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

class Installation extends Model
{
    protected $table = 'installations';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'installation_ip',
        'installation_domain',
        'installation_path',
        'installation_date',
        'installation_status',
        'installation_hash',
        'installation_disable_ip_verification',
        'version',
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

    #[Scope]
    protected function active($query)
    {
        return $query->where('installation_status', 1);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
