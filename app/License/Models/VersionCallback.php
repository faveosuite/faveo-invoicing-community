<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VersionCallback extends Model
{
    protected $table = 'version_callbacks';

    protected $fillable = [
        'product_id',
        'version_id',
        'callback_type',
        'callback_ip',
        'callback_path',
        'callback_date_time',
        'callback_status',
    ];

    protected $casts = [
        'callback_date_time' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\ProductUpload::class, 'version_id');
    }
}
