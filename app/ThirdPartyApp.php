<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyApp extends Model
{
    protected $table = 'third_party_apps';

    protected $fillable = ['app_name', 'app_key', 'app_secret'];
}
