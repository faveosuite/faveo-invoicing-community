<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory;
    protected $table = 'api_keys';

    protected $fillable = ['rzp_key', 'rzp_secret', 'apilayer_key', 'bugsnag_api_key',
        'zoho_api_key', 'msg91_auth_key', 'msg91_sender', 'msg91_template_id', 'twitter_consumer_key',
        'twitter_consumer_secret', 'twitter_access_token', 'access_tooken_secret', 'license_api_secret', 'license_api_url', 'nocaptcha_sitekey', 'captcha_secretCheck', 'update_api_url', 'update_api_secret', 'terms_url', 'pipedrive_api_key', 'stripe_key', 'stripe_secret', 'license_client_id', 'license_client_secret', 'license_grant_type', 'msg91_third_party_id', 'require_pipedrive_user_verification'];
}
