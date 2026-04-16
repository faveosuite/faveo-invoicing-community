<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflFailedLogins extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'failed_login_id';
}
