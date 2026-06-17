<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseBannedHost extends Model
{
    protected $table = 'license_banned_hosts';

    protected $fillable = [
        'banned_host_ip',
        'comments',
    ];
}
