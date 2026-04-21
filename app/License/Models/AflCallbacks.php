<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AflCallbacks extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'callback_id';

    public $timestamps = false;

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
