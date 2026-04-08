<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseBannedHost extends Model
{
    protected $table = 'license_banned_hosts';

    protected $fillable = [
        'banned_host_ip',
        'comments',
    ];
}
