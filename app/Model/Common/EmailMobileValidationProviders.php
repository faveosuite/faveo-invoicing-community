<?php

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

class EmailMobileValidationProviders extends Model
{
    protected $table = 'email_mobile_validation_providers';

    protected $fillable = ['provider,api_key', 'api_secret', 'mode', 'accepted_output'];
}
