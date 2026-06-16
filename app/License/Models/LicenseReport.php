<?php

namespace App\License\Models;

use Override;
use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseReport extends Model
{
    protected $table = 'license_reports';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'report_date_time',
        'report_text',
        'report_system',
        'report_status',
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
    protected function pending($query)
    {
        return $query->where('report_status', 0);
    }
    #[Override]
    protected function casts(): array
    {
        return [
            'report_date_time' => 'datetime',
            'report_system' => 'integer',
            'report_status' => 'integer',
            'product_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
