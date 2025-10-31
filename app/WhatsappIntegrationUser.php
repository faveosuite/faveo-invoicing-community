<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WhatsappIntegrationUser extends Model
{
    protected $table = 'whatsapp_integration_user';

    protected $fillable = ['waba_id', 'phone_number_id', 'phone_number', 'user_id', 'access_token', 'user_callback_url', 'business_id'];
}
