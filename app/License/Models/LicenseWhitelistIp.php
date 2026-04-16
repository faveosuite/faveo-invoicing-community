<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseWhitelistIp extends Model
{
    protected $table = 'license_whitelist_ips';

    protected $fillable = [
        'whitelist_host_ip',
    ];
}
