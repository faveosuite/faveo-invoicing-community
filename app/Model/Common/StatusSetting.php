<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Database\Factories\StatusSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusSetting extends Model
{
    use HasFactory, SystemActivityLogsTrait;

    protected $table = 'status_settings';

    public $timestamps = false;

    protected $fillable = ['expiry_mail', 'subs_expirymail', 'activity_log_delete', 'license_status', 'use_streams', 'github_status', 'mailchimp_status', 'twitter_status', 'msg91_status', 'emailverification_status', 'recaptcha_status', 'update_status', 'zoho_status', 'rzp_status', 'mailchimp_product_status', 'mailchimp_ispaid_status', 'terms', 'pipedrive_status', 'domain_check', 'msg91_report_delete_status', 'email_validation_status', 'cloud_button', 'reoon_deletion_status', 'whatsapp_status'];

    protected $logName = 'api_key';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'expiry_mail',
        'subs_expirymail',
        'activity_log_delete',
        'license_status',
        'github_status',
        'mailchimp_status',
        'twitter_status',
        'msg91_status',
        'emailverification_status',
        'recaptcha_status',
        'update_status',
        'zoho_status',
        'rzp_status',
        'mailchimp_product_status',
        'mailchimp_ispaid_status',
        'terms',
        'pipedrive_status',
        'domain_check',
        'msg91_report_delete_status',
        'email_validation_status',
        'cloud_button',
    ];

    protected $logUrl = [
        'segments' => ['third-party-integration'],
    ];

    protected function getMappings(): array
    {
        return [
            'expiry_mail' => ['Expiry Mail', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'subs_expirymail' => ['Subscription Expiry Mail', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'activity_log_delete' => ['Activity Log Delete', fn ($value) => (int) $value],
            'license_status' => ['License Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'github_status' => ['Github Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_status' => ['Mailchimp Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'twitter_status' => ['Twitter Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'msg91_status' => ['Msg91 Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'emailverification_status' => ['Email Verification Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'recaptcha_status' => ['Recaptcha Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'update_status' => ['Update Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'zoho_status' => ['Zoho Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'rzp_status' => ['Razorpay Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_product_status' => ['Mailchimp Product Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_ispaid_status' => ['Mailchimp Is Paid Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'terms' => ['Terms and Condition', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'pipedrive_status' => ['Pipedrive Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'domain_check' => ['Domain Check', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'msg91_report_delete_status' => ['Msg91 Report Delete Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'email_validation_status' => ['Email Validation Status', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'cloud_button' => ['Cloud Free Trial', fn ($value) => (int) $value === 1 ? __('message.enable') : __('message.disable')],
        ];
    }

    public function getLogUrl($id = null): ?string
    {
        $fields = ['emailverification_status', 'msg91_status'];
        $cloud = ['cloud_button'];

        if ($this->wasChanged($fields)) {
            return url('contact-option');
        }

        if ($this->wasChanged($cloud)) {
            return url('view/tenant');
        }

        return url('third-party-integration');
    }

    public function getLogName()
    {
        $fields = ['emailverification_status', 'msg91_status'];
        $cloud = ['cloud_button'];

        if ($this->wasChanged($fields)) {
            return 'contact_options';
        }

        if ($this->wasChanged($cloud)) {
            return 'cloud';
        }

        return 'api_key';
    }

    protected static function newFactory()
    {
        return StatusSettingFactory::new();
    }
}
