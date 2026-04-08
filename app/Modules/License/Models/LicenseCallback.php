<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseCallback extends Model
{
    protected $table = 'license_callbacks';

    protected $fillable = [
        'product_id',
        'client_id',
        'license_code',
        'callback_ip',
        'callback_domain',
        'callback_date_time',
        'callback_status',
    ];

    protected $casts = [
        'callback_date_time' => 'datetime',
        'callback_status'    => 'integer',
        'product_id'         => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product_id');
    }
}
