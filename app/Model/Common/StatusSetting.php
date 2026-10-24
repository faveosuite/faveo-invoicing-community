<?php

namespace App\Model\Common;

use App\Traits\SystemActivityLogsTrait;
use Database\Factories\StatusSettingFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property int $github_status
 * @property int $mailchimp_status
 * @property int $twitter_status
 * @property int $msg91_status
 * @property int|null $mobileverification_status
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
 * @property int $stripe_auto_renewal
 * @property int $razorpay_auto_renewal
 * @property string $cloud_button
 * @property int $whatsapp_status
 * @property int $open_payment_status
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 *
 * @method static StatusSettingFactory factory($count = null, $state = [])
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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StatusSetting whereOpenPaymentStatus($value)
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
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    use SystemActivityLogsTrait;

    protected $table = 'status_settings';

    public $timestamps = false;

    protected $fillable = ['stripe_auto_renewal', 'razorpay_auto_renewal', 'github_status', 'mailchimp_status', 'twitter_status', 'msg91_status', 'mobileverification_status', 'emailverification_status', 'recaptcha_status', 'update_status', 'zoho_status', 'rzp_status', 'mailchimp_product_status', 'mailchimp_ispaid_status', 'terms', 'pipedrive_status', 'domain_check', 'email_validation_status', 'cloud_button', 'whatsapp_status'];

    protected string $logName = 'api_key';

    protected string $logNameColumn = 'Settings';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'github_status',
        'mailchimp_status',
        'twitter_status',
        'msg91_status',
        'mobileverification_status',
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
        'email_validation_status',
        'cloud_button',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['third-party-integration'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'github_status' => ['Github Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mailchimp_status' => ['Mailchimp Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'twitter_status' => ['Twitter Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'msg91_status' => ['Msg91 Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'mobileverification_status' => ['Mobile Verification Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
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
            'email_validation_status' => ['Email Validation Status', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
            'cloud_button' => ['Cloud Free Trial', fn ($value): array|string => (int) $value === 1 ? __('message.enable') : __('message.disable')],
        ];
    }

    /**
     * Whether auto-renewal can actually run for a given gateway right now —
     * the single authoritative check, used both to decide whether to offer
     * it (checkout modal, the order page's "Enable auto-renewal" tab) and to
     * gate actually activating it (never trust a flag captured earlier, at
     * checkout time, against settings that could change before payment
     * actually completes). Two independent toggles, both required:
     * Setting.autorenewal_status (a global kill switch, Company Settings)
     * AND this gateway's own {stripe,razorpay}_auto_renewal flag (Payment
     * Gateway settings).
     */
    public static function autoRenewalEnabledFor(string $gateway): bool
    {
        if (! (bool) Setting::find(1)?->autorenewal_status) {
            return false;
        }

        $status = static::first(['stripe_auto_renewal', 'razorpay_auto_renewal']);

        return match (strtolower($gateway)) {
            'stripe' => (bool) $status?->stripe_auto_renewal,
            'razorpay' => (bool) $status?->razorpay_auto_renewal,
            default => false,
        };
    }

    /**
     * Whether users must verify their mobile by OTP: the admin wants it (Contact Options) and MSG91
     * is on to send it. msg91_status alone only says SMS can be sent.
     */
    public function mobileVerificationEnabled(): bool
    {
        return (int) $this->msg91_status === 1 && (int) $this->mobileverification_status === 1;
    }

    public function getLogUrl(mixed $id = null): ?string
    {
        $fields = ['emailverification_status', 'mobileverification_status'];
        $cloud = ['cloud_button'];

        if ($this->wasChanged($fields)) {
            return url('admin/settings/contact-options');
        }

        if ($this->wasChanged($cloud)) {
            return url('admin/settings/cloud-details');
        }

        return url('admin/settings/api/third-party');
    }

    public function getLogName(): string
    {
        $fields = ['emailverification_status', 'mobileverification_status'];
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
