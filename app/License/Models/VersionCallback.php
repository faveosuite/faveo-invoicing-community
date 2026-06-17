<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $version_id
 * @property string $callback_type
 * @property string|null $callback_ip
 * @property string|null $callback_path
 * @property \Illuminate\Support\Carbon $callback_date_time
 * @property string|null $callback_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @property-read ProductUpload|null $version
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCallbackDateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCallbackIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCallbackPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCallbackStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCallbackType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionCallback whereVersionId($value)
 * @mixin \Eloquent
 */
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
