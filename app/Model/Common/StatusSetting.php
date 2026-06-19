<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Database\Factories\StatusSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $expiry_mail
 * @property int $activity_log_delete
 * @property int $github_status
 * @property int $mailchimp_status
 * @property int $twitter_status
 * @property int $msg91_status
 * @property int $emailverification_status
 * @property int $recaptcha_status
 * @property int $email_validation_status
 * @property int $mobile_validation_status
 * @property int|null $update_settings
 * @property int|null $zoho_status
 * @property int|null $rzp_status
 * @property int|null $mailchimp_product_status
 * @property int|null $mailchimp_ispaid_status
 * @property int|null $terms
 * @property int|null $pipedrive_status
 * @property int $domain_check
 * @property int $subs_expirymail
 * @property int $stripe_auto_renewal
 * @property int $razorpay_auto_renewal
 * @property int $post_expirymail
 * @property string $cloud_button
 * @property int $cloud_mail_status
 * @property int $invoice_deletion_status
 * @property int $reoon_deletion_status
 * @property string|null $msg91_report_delete_status
 * @property int $system_log_status
 * @property int $whatsapp_status
 * @property int $installation_logs_status
 * @property int $license_reports_cleanup_status
 * @property int $license_callbacks_cleanup_status
 * @property int $license_crack_reports_cleanup_status
 * @property int $license_system_reports_cleanup_status
 * @property int $license_versions_cleanup_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static \Database\Factories\StatusSettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereActivityLogDelete($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereCloudButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereCloudMailStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereDomainCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereEmailValidationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereEmailverificationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereExpiryMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereGithubStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereInstallationLogsStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereInvoiceDeletionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereLicenseCallbacksCleanupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereLicenseCrackReportsCleanupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereLicenseReportsCleanupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereLicenseSystemReportsCleanupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereLicenseVersionsCleanupStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMailchimpIspaidStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMailchimpProductStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMailchimpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMobileValidationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMsg91ReportDeleteStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereMsg91Status($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting wherePipedriveStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting wherePostExpirymail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereRazorpayAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereRecaptchaStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereReoonDeletionStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereRzpStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereStripeAutoRenewal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereSubsExpirymail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereSystemLogStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereTwitterStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereUpdateSettings($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereWhatsappStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereZohoStatus($value)
 *
 * @mixin \Eloquent
 */
class StatusSetting extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'status_settings';

    public $timestamps = false;

    protected $fillable = ['expiry_mail', 'subs_expirymail', 'stripe_auto_renewal', 'razorpay_auto_renewal', 'activity_log_delete', 'github_status', 'mailchimp_status', 'twitter_status', 'msg91_status', 'emailverification_status', 'recaptcha_status', 'update_status', 'zoho_status', 'rzp_status', 'mailchimp_product_status', 'mailchimp_ispaid_status', 'terms', 'pipedrive_status', 'domain_check', 'msg91_report_delete_status', 'email_validation_status', 'cloud_button', 'reoon_deletion_status', 'whatsapp_status', 'installation_logs_status', 'license_reports_cleanup_status', 'license_callbacks_cleanup_status', 'license_crack_reports_cleanup_status', 'license_system_reports_cleanup_status', 'license_versions_cleanup_status'];

    protected string $logName = 'api_key';

    protected string $logNameColumn = 'Settings';

    protected array $logAttributes = [
        'expiry_mail',
        'subs_expirymail',
        'activity_log_delete',
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

    protected array $logUrl = [
        'segments' => ['third-party-integration'],
    ];

    protected function getMappings(): array
    {
        return [
            'expiry_mail' => ['Expiry Mail', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'subs_expirymail' => ['Subscription Expiry Mail', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'activity_log_delete' => ['Activity Log Delete', fn ($value): int => (int) $value],
            'github_status' => ['Github Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_status' => ['Mailchimp Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'twitter_status' => ['Twitter Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'msg91_status' => ['Msg91 Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'emailverification_status' => ['Email Verification Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'recaptcha_status' => ['Recaptcha Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'update_status' => ['Update Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'zoho_status' => ['Zoho Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'rzp_status' => ['Razorpay Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_product_status' => ['Mailchimp Product Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_ispaid_status' => ['Mailchimp Is Paid Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'terms' => ['Terms and Condition', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'pipedrive_status' => ['Pipedrive Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'domain_check' => ['Domain Check', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'msg91_report_delete_status' => ['Msg91 Report Delete Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'email_validation_status' => ['Email Validation Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'cloud_button' => ['Cloud Free Trial', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
        ];
    }

    public function getLogUrl(mixed $id = null): ?string
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

    public function getLogName(): string
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

    protected static function newFactory(): mixed
    {
        return StatusSettingFactory::new();
    }
}
