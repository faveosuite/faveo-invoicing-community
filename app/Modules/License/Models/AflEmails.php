<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AflEmails extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $primaryKey = 'email_id';
}
