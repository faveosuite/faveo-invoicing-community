<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class CreditActivity extends Model
{
    protected $table = 'credit_activity';

    protected $fillable = ['payment_id','text','role','created_at','updated_at'];
}
