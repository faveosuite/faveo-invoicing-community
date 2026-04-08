<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVersion extends Model
{
    protected $table = 'product_versions';

    protected $fillable = [
        'product_id',
        'version_number',
        'version_install_file',
        'version_install_query',
        'version_raw_install_query',
        'version_upgrade_file',
        'version_upgrade_query',
        'version_raw_upgrade_query',
        'version_install_limit',
        'version_install_count',
        'version_upgrade_limit',
        'version_upgrade_count',
        'version_changelog',
        'version_date',
        'version_expire_date',
        'version_comments',
        'version_status',
        'expired',
    ];

    protected $casts = [
        'version_status' => 'integer',
        'product_id' => 'integer',
        'expired' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product_id');
    }

    public function callbacks(): HasMany
    {
        return $this->hasMany(VersionCallback::class, 'version_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(VersionInstallation::class, 'version_id');
    }

    /**
     * Scope: active versions (status = 1).
     */
    public function scopeActive($query)
    {
        return $query->where('version_status', 1);
    }

    /**
     * Scope: order by latest.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('version_date', 'desc');
    }
}
