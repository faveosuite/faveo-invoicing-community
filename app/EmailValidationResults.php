<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailValidationResults extends Model
{
    protected $table = 'email_validation_results';
    protected $fillable = ['email', 'method', 'status', 'result', 'state', 'town', 'first_name', 'last_name', 'company', 'address', 'registration', 'mobile',
        'mobile_code', 'country', 'mobile_country_iso'];
}
