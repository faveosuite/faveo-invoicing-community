<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionInstallation extends Model
{
    protected $table = 'version_installations';

    protected $fillable = [
        'product_id',
        'version_id',
        'installation_ip',
        'installation_path',
        'installation_date',
        'installation_status',
    ];

    protected $casts = [
        'installation_status' => 'integer',
        'product_id'          => 'integer',
        'version_id'          => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(ProductVersion::class, 'version_id');
    }

    public function scopeActive($query)
    {
        return $query->where('installation_status', 1);
    }
}
