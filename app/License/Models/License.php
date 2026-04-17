<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    protected $casts = [
        'license_require_domain' => 'boolean',
        'license_limit' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'license_code', 'license_code');
    }

    public function callbacks(): HasMany
    {
        return $this->hasMany(LicenseCallback::class, 'license_code', 'license_code');
    }

    public function plugins(): HasMany
    {
        return $this->hasMany(LicensePlugin::class, 'license_id', 'id');
    }

    public function addonProducts(): BelongsToMany
    {
        return $this->belongsToMany(\App\Model\Product\Product::class, 'license_plugins', 'license_id', 'product_id')->withTimestamps();
    }

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
        return (string) $this->license_status === '1' || $this->license_status === 'active';
    }

    public function isExpired(): bool
    {
        return ! empty($this->license_expire_date) && $this->license_expire_date < now()->toDateTimeString();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('license_status', ['1', 1, 'active']);
    }

    public function scopeSuspended($query)
    {
        return $query->whereIn('license_status', ['2', 2, 'suspended']);
    }
}
