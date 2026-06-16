<?php

namespace App\License\Models;

use Override;
use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionInstallation extends Model
{
    protected $table = 'version_installations';

    protected $fillable = [
        'product_id',
        'user_id',
        'version_id',
        'installation_date',
        'installation_status',
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

    #[Scope]
    protected function active($query)
    {
        return $query->where('installation_status', 1);
    }
    #[Override]
    protected function casts(): array
    {
        return [
            'installation_status' => 'integer',
            'product_id' => 'integer',
            'version_id' => 'integer',
        ];
    }
}
