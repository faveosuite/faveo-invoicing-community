<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseScheme extends Model
{
    protected $table = 'license_schemes';

    protected $fillable = [
        'scheme_query',
        'scheme_status',
    ];
}
