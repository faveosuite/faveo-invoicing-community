<?php

namespace App\License\Models;

use Override;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionCallback extends Model
{
    protected $table = 'version_callbacks';

    protected $fillable = [
        'product_id',
        'version_id',
        'callback_type',
        'callback_ip',
        'callback_path',
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
     * @return BelongsTo<ProductUpload, $this>
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(ProductUpload::class, 'version_id');
    }
    #[Override]
    protected function casts(): array
    {
        return [
            'callback_date_time' => 'datetime',
        ];
    }
}
