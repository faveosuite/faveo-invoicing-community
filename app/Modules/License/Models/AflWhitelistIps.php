<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflWhitelistIps extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $primaryKey = 'whitelist_host_id';

    public $timestamps = true;
}
