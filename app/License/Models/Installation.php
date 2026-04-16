<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installation extends Model
{
    protected $table = 'installations';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'installation_ip',
        'installation_domain',
        'installation_date',
        'installation_status',
        'installation_hash',
    ];

    protected $casts = [
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

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class, 'license_code', 'license_code');
    }

    public function scopeActive($query)
    {
        return $query->where('installation_status', 1);
    }
}
