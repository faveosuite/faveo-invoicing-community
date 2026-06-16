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
