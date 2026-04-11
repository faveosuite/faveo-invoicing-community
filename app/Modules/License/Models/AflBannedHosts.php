<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflBannedHosts extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'banned_host_id';

    public $timestamps = false;
    //protected $table = 'afl_banned_host';
}
