<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LicenseReport extends Model
{
    protected $table = 'license_reports';

    protected $fillable = [
        'product_id',
        'user_id',
        'license_code',
        'report_date_time',
        'report_text',
        'report_system',
        'report_status',
    ];

    protected $casts = [
        'report_date_time' => 'datetime',
        'report_system' => 'integer',
        'report_status' => 'integer',
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

    public function scopePending($query)
    {
        return $query->where('report_status', 0);
    }
}
