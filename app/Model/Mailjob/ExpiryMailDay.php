<?php

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;

class ExpiryMailDay extends Model
{
    protected $table = 'expiry_mail_days';

    protected $fillable = ['days', 'autorenewal_days', 'postexpiry_days', 'reoon_logs_days'];
}
