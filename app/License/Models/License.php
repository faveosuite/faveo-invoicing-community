<?php

namespace App\License\Models;

use App\Model\Product\Product;
use App\User;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property string $license_code
 * @property string|null $license_order_number
 * @property string|null $license_ip
 * @property string|null $license_domain
 * @property bool $license_require_domain
 * @property int $license_limit
 * @property string|null $license_date
 * @property string|null $license_cancel_date
 * @property string|null $license_expire_date
 * @property string|null $license_expire_email_date
 * @property string|null $license_updates_date
 * @property string|null $license_updates_email_date
 * @property string|null $license_support_date
 * @property string|null $license_support_email_date
 * @property string|null $license_comments
 * @property int $license_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Product> $addonProducts
 * @property-read int|null $addon_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\License\Models\LicenseCallback> $callbacks
 * @property-read int|null $callbacks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\License\Models\Installation> $installations
 * @property-read int|null $installations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\License\Models\LicenseOption> $licenseOptions
 * @property-read int|null $license_options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\License\Models\LicenseOption> $options
 * @property-read int|null $options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\License\Models\LicensePlugin> $plugins
 * @property-read int|null $plugins_count
 * @property-read Product $product
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License suspended()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseCancelDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseExpireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseExpireEmailDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseRequireDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseSupportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseSupportEmailDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseUpdatesDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereLicenseUpdatesEmailDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|License whereUserId($value)
 * @mixin \Eloquent
 */
class License extends Model
{
    protected $table = 'licenses';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'license_order_number',
        'license_ip',
        'license_domain',
        'license_require_domain',
        'license_limit',
        'license_date',
        'license_cancel_date',
        'license_expire_date',
        'license_expire_email_date',
        'license_updates_date',
        'license_updates_email_date',
        'license_support_date',
        'license_support_email_date',
        'license_comments',
        'license_status',
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
     * @return HasMany<Installation, $this>
     */
    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'license_code', 'license_code');
    }

    /**
     * @return HasMany<LicenseCallback, $this>
     */
    public function callbacks(): HasMany
    {
        return $this->hasMany(LicenseCallback::class, 'license_code', 'license_code');
    }

    /**
     * @return HasMany<LicensePlugin, $this>
     */
    public function plugins(): HasMany
    {
        return $this->hasMany(LicensePlugin::class, 'license_id', 'id');
    }

    /**
     * @return BelongsToMany<Product, $this, Pivot>
     */
    public function addonProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'license_plugins', 'license_id', 'product_id')->withTimestamps();
    }

    /**
     * @return HasMany<LicenseOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(LicenseOption::class, 'option_group', 'id');
    }

    public function licenseOptions(): HasMany
    {
        return $this->options();
    }

    public function isActive(): bool
    {
        return $this->license_status === 1;
    }

    public function isExpired(): bool
    {
        return ! empty($this->license_expire_date) && $this->license_expire_date < now()->toDateTimeString();
    }

    #[Scope]
    protected function active($query)
    {
        return $query->where('license_status', 1);
    }

    #[Scope]
    protected function suspended($query)
    {
        return $query->where('license_status', 2);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'license_require_domain' => 'boolean',
            'license_limit' => 'integer',
            'license_status' => 'integer',
            'product_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
