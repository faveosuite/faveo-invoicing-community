<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WhatsappIntegration extends Model
{
    protected $table = 'whatsapp_integration';
    protected $fillable = ['app_id', 'app_secret', 'verify_token', 'callback_url', 'config_id'];
}
