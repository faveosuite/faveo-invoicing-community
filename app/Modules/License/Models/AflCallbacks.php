<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AflCallbacks extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'callback_id';

    public $timestamps = false;

    public function product()
    {
        return $this->belongsTo(AflProducts::class, 'product_id', 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(AflClients::class, 'client_id', 'client_id');
    }

    public function license()
    {
        return $this->belongsTo(AflLicenses::class, 'license_code', 'license_code');
    }

    public function scopeWithClientEmailOrLicenseCode($query)
    {
        $query->addSelect([DB::raw('
                CASE
                    WHEN client_id IS NOT NULL THEN (SELECT client_email FROM users WHERE client_id = afl_callbacks.client_id LIMIT 1)
                    ELSE license_code
                END As client_email_or_license_code
                ')]);
    }
}
