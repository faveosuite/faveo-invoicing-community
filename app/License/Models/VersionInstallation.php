<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\Model\Product\ProductUpload;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property int $version_id
 * @property string|null $installation_date
 * @property int $installation_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Product $product
 * @property-read ProductUpload $version
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereInstallationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereInstallationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|VersionInstallation whereVersionId($value)
 *
 * @mixin \Eloquent
 */
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

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    #[Scope]
    protected function active(\Illuminate\Database\Eloquent\Builder $query): mixed
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
