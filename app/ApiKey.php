<?php

namespace App;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'api_keys';

    protected $fillable = ['rzp_key', 'rzp_secret', 'apilayer_key', 'bugsnag_api_key',
        'zoho_api_key', 'msg91_auth_key', 'msg91_sender', 'msg91_template_id', 'twitter_consumer_key',
        'twitter_consumer_secret', 'twitter_access_token', 'access_tooken_secret', 'license_api_secret', 'license_api_url', 'nocaptcha_sitekey', 'captcha_secretCheck', 'update_api_url', 'update_api_secret', 'terms_url', 'pipedrive_api_key', 'stripe_key', 'stripe_secret', 'license_client_id', 'license_client_secret', 'license_grant_type', 'msg91_third_party_id', 'require_pipedrive_user_verification', 'verification_preference'];


    protected $logName = 'api_key';

    protected $logNameColumn = 'apikeys';


    protected $logAttributes = [
        'rzp_key', 'rzp_secret', 'apilayer_key', 'bugsnag_api_key',
        'zoho_api_key', 'msg91_auth_key', 'msg91_sender', 'msg91_template_id',
        'twitter_consumer_key', 'twitter_consumer_secret', 'twitter_access_token',
        'access_tooken_secret', 'license_api_secret', 'license_api_url',
        'nocaptcha_sitekey', 'captcha_secretCheck', 'update_api_url',
        'update_api_secret', 'terms_url', 'pipedrive_api_key', 'stripe_key',
        'stripe_secret', 'license_client_id', 'license_client_secret',
        'license_grant_type', 'msg91_third_party_id',
        'require_pipedrive_user_verification', 'verification_preference'
    ];

    protected $logUrl = ['/third-party-integration'];


    protected function getMappings(): array
    {
        return [
            'rzp_key' => ['Rzp Key', fn($value) => $value],
            'rzp_secret' => ['Rzp Secret', fn($value) => $value],
            'apilayer_key' => ['Apilayer Key', fn($value) => $value],
            'bugsnag_api_key' => ['Bugsnag Api Key', fn($value) => $value],
            'zoho_api_key' => ['Zoho Api Key', fn($value) => $value],
            'msg91_auth_key' => ['Msg91 Auth Key', fn($value) => $value],
            'msg91_sender' => ['Msg91 Sender', fn($value) => $value],
            'msg91_template_id' => ['Msg91 Template Id', fn($value) => $value],
            'twitter_consumer_key' => ['Twitter Consumer Key', fn($value) => $value],
            'twitter_consumer_secret' => ['Twitter Consumer Secret', fn($value) => $value],
            'twitter_access_token' => ['Twitter Access Token', fn($value) => $value],
            'access_tooken_secret' => ['Access Token Secret', fn($value) => $value],
            'license_api_secret' => ['License Api Secret', fn($value) => $value],
            'license_api_url' => ['License Api Url', fn($value) => $value],
            'nocaptcha_sitekey' => ['Captcha Site key', fn($value) => $value],
            'captcha_secretCheck' => ['Captcha Secret Key', fn($value) => $value],
            'update_api_url' => ['Update Api Url', fn($value) => $value],
            'update_api_secret' => ['Update Api Secret', fn($value) => $value],
            'terms_url' => ['Terms Url', fn($value) => $value],
            'pipedrive_api_key' => ['Pipedrive Api Key', fn($value) => $value],
            'stripe_key' => ['Stripe Key', fn($value) => $value],
            'stripe_secret' => ['Stripe Secret', fn($value) => $value],
            'license_client_id' => ['License Client Id', fn($value) => $value],
            'license_client_secret' => ['License Client Secret', fn($value) => $value],
            'license_grant_type' => ['License Grant Type', fn($value) => $value],
            'msg91_third_party_id' => ['Msg91 Third Party Id', fn($value) => $value],
            'require_pipedrive_user_verification' => ['Require Pipedrive User Verification', fn($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'verification_preference' => ['Verification Preference', fn($value) => $value],
        ];
    }

}
