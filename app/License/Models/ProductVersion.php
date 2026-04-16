<?php

namespace App\License\Models;

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
        'version_upgrade_file',
        'version_changelog',
        'version_date',
        'version_expire_date',
        'version_status',
    ];

    protected $casts = [
        'product_id' => 'integer',
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
