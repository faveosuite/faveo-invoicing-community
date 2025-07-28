<?php

namespace App\Model\Common;

use Database\Factories\StatusSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusSetting extends Model
{
    use HasFactory;

    protected $table = 'status_settings';

    public $timestamps = false;

    protected $fillable = ['expiry_mail', 'subs_expirymail', 'activity_log_delete', 'license_status', 'github_status', 'mailchimp_status', 'twitter_status', 'msg91_status', 'emailverification_status', 'recaptcha_status', 'v3_recaptcha_status', 'update_status', 'zoho_status', 'rzp_status', 'mailchimp_product_status', 'mailchimp_ispaid_status', 'terms', 'pipedrive_status', 'domain_check', 'v3_v2_recaptcha_status', 'msg91_report_delete_status', 'email_validation_status'];

    protected static function newFactory()
    {
        return StatusSettingFactory::new();
    }
}
