<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property string $license_code
 * @property string|null $installation_ip
 * @property string|null $installation_domain
 * @property string|null $installation_path
 * @property string|null $installation_date
 * @property int $installation_status
 * @property string|null $installation_hash
 * @property string|null $version
 * @property int $installation_disable_ip_verification
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\License\Models\License|null $license
 * @property-read Product $product
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationDisableIpVerification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationHash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereInstallationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereLicenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installation whereVersion($value)
 * @mixin \Eloquent
 */
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

        /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
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
            'product_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
