<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'license_require_domain' => 'integer',
        'license_limit' => 'integer',
        'license_status' => 'integer',
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

    public function options(): HasMany
    {
        return $this->hasMany(LicenseOption::class, 'license_id', 'id');
    }

    /**
     * Check if license is active (status = 1).
     */
    public function isActive(): bool
    {
        return $this->license_status == 1;
    }

    /**
     * Check if license is expired.
     */
    public function isExpired(): bool
    {
        return $this->license_expire_date && $this->license_expire_date < now()->format('Y-m-d');
    }

    /**
     * Scope: active licenses only (status = 1).
     */
    public function scopeActive($query)
    {
        return $query->where('license_status', 1);
    }

    /**
     * Scope: suspended licenses (status = 2).
     */
    public function scopeSuspended($query)
    {
        return $query->where('license_status', 2);
    }
}
