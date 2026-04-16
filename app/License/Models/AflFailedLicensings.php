<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflFailedLicensings extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'failed_licensing_id';
}
