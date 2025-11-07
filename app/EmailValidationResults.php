<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailValidationResults extends Model
{
    protected $table = 'email_validation_results';
    protected $fillable = ['email','method','status','result'];
}
