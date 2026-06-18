<?php

namespace App\Model\Common;

use App\Facades\Attach;
use App\Model\Payment\Currency;
use App\Traits\SystemActivityLogsTrait;
use Crypt;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $company
 * @property string $website
 * @property string $phone
 * @property string|null $phone_country_iso
 * @property string $logo
 * @property string $address
 * @property string $driver
 * @property string $host
 * @property int $port
 * @property string $encryption
 * @property string $email
 * @property string $password
 * @property int $error_log
 * @property int $invoice
 * @property int $subscription_over
 * @property int $subscription_going_to_end
 * @property int $forgot_password
 * @property int $order_mail
 * @property int $welcome_mail
 * @property int $download
 * @property int $invoice_template
 * @property string $city
 * @property \App\Model\Common\State|null $state
 * @property \App\Model\Common\Country|null $country
 * @property int $timezone_id
 * @property string $date_format
 * @property string $time_format
 * @property string|null $content
 * @property string $error_email
 * @property string|null $title
 * @property string|null $admin_logo
 * @property string|null $fav_icon
 * @property string|null $favicon_title
 * @property string|null $company_email
 * @property string|null $favicon_title_client
 * @property string|null $default_currency
 * @property string|null $default_symbol
 * @property string|null $file_storage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $cin_no
 * @property string|null $gstin
 * @property string|null $key
 * @property string|null $secret
 * @property string|null $region
 * @property string|null $domain
 * @property int $sending_status
 * @property string|null $zip
 * @property string|null $version
 * @property string|null $password_mail
 * @property string|null $payment_successfull
 * @property string|null $payment_failed
 * @property string|null $card_failed
 * @property string|null $autosubscription_going_to_end
 * @property string|null $free_trail_expired
 * @property string|null $Free_trail_gonna_expired
 * @property string|null $cloud_order
 * @property string|null $cloud_deleted
 * @property string|null $from_name
 * @property string|null $phone_code
 * @property string|null $knowledge_base_url
 * @property int $autorenewal_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Currency|null $defaultCurrency
 * @property-read \App\Model\Common\Language|null $language
 * @property-read \App\Model\Common\Timezone|null $timezone
 *
 * @method static \Database\Factories\Model\Common\SettingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAdminLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutorenewalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereAutosubscriptionGoingToEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCardFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCinNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCloudDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCloudOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCompanyEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDefaultSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDownload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereErrorEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereErrorLog($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFavIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFaviconTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFaviconTitleClient($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFileStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereForgotPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFreeTrailExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFreeTrailGonnaExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereGstin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereInvoiceTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKnowledgeBaseUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereOrderMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePasswordMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePaymentFailed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePaymentSuccessfull($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePhoneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePhoneCountryIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSendingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSubscriptionGoingToEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereSubscriptionOver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTimeFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTimezoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWebsite($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereWelcomeMail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereZip($value)
 *
 * @mixin \Eloquent
 */
class Setting extends Model
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'settings';

    protected $fillable = ['company', 'website', 'phone', 'logo', 'phone_country_iso',
        'address', 'host', 'port', 'encryption', 'email', 'password',
        'error_log', 'error_email', 'state', 'city', 'country',
        'invoice', 'download', 'subscription_over', 'subscription_going_to_end',
        'forgot_password', 'order_mail', 'welcome_mail', 'invoice_template',
        'autosubscription_going_to_end', 'payment_successfull', 'payment_failed',
        'cloud_deleted', 'cloud_order',
        'driver', 'admin_logo', 'title', 'favicon_title', 'fav_icon',
        'company_email', 'favicon_title_client', 'default_currency', 'default_symbol', 'file_storage', 'cin_no', 'gstin', 'zip', 'from_name', 'phone_code', 'knowledge_base_url', 'content', 'autorenewal_status', 'sending_status',
        'timezone_id', 'date_format', 'time_format'];

    protected string $logName = 'settings';

    protected string $logNameColumn = 'Company Settings';

    protected array $logAttributes = [
        'company', 'website', 'phone', 'logo', 'phone_country_iso',
        'address', 'host', 'port', 'encryption', 'email', 'password',
        'error_log', 'error_email', 'state', 'city', 'country',
        'invoice', 'download', 'subscription_over', 'subscription_going_to_end',
        'forgot_password', 'order_mail', 'welcome_mail', 'invoice_template',
        'autosubscription_going_to_end', 'payment_successfull', 'payment_failed',
        'cloud_deleted', 'cloud_order',
        'driver', 'admin_logo', 'title', 'favicon_title', 'fav_icon',
        'company_email', 'favicon_title_client', 'default_currency', 'default_symbol', 'file_storage', 'cin_no', 'gstin', 'zip', 'from_name', 'phone_code', 'knowledge_base_url', 'content',
        'sending_status',
    ];

    protected array $logUrl = [
        'segments' => ['settings/system'],
    ];

    protected function getMappings(): array
    {
        return [
            'company' => ['Company', fn ($value) => $value],
            'website' => ['Website', fn ($value) => $value],
            'phone' => ['Phone', fn ($value) => $value],
            'logo' => ['Logo', fn ($value) => $value],
            'phone_country_iso' => ['Phone Country ISO', fn ($value) => $value],
            'address' => ['Address', fn ($value) => $value],
            'host' => ['Host', fn ($value) => $value],
            'port' => ['Port', fn ($value) => $value],
            'encryption' => ['Encryption', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'error_log' => ['Error Log', fn ($value) => $value],
            'error_email' => ['Error Email', fn ($value) => $value],
            'state' => ['State', fn ($value) => $value],
            'city' => ['City', fn ($value) => $value],
            'country' => ['Country', fn ($value) => $value],
            'invoice' => ['Invoice', fn ($value) => $value],
            'download' => ['Download', fn ($value) => $value],
            'subscription_over' => ['Subscription Over', fn ($value) => $value],
            'subscription_going_to_end' => ['Subscription Going To End', fn ($value) => $value],
            'forgot_password' => ['Forgot Password', fn ($value) => $value],
            'order_mail' => ['Order Mail', fn ($value) => $value],
            'welcome_mail' => ['Welcome Mail', fn ($value) => $value],
            'invoice_template' => ['Invoice Template', fn ($value) => $value],
            'driver' => ['Driver', fn ($value) => $value],
            'admin_logo' => ['Admin Logo', fn ($value) => $value],
            'title' => ['Title', fn ($value) => $value],
            'favicon_title' => ['Favicon Title', fn ($value) => $value],
            'fav_icon' => ['Fav Icon', fn ($value) => $value],
            'company_email' => ['Company Email', fn ($value) => $value],
            'favicon_title_client' => ['Favicon Title (Client)', fn ($value) => $value],
            'default_currency' => ['Default Currency', fn ($value) => $value],
            'default_symbol' => ['Default Symbol', fn ($value) => $value],
            'file_storage' => ['File Storage', fn ($value) => $value],
            'cin_no' => ['CIN Number', fn ($value) => $value],
            'gstin' => ['GSTIN', fn ($value) => $value],
            'zip' => ['ZIP', fn ($value) => $value],
            'from_name' => ['From Name', fn ($value) => $value],
            'phone_code' => ['Phone Code', fn ($value) => $value],
            'knowledge_base_url' => ['Knowledge Base URL', fn ($value) => $value],
            'content' => ['Content', fn ($value) => $value],
        ];
    }

    protected function password(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            if ($value) {
                return Crypt::decrypt($value);
            }

            return $value;
        }, set: function ($value): array {
            $value = Crypt::encrypt($value);

            return ['password' => $value];
        });
    }

    public function getImage(?string $value, string $path, $default = null)
    {
        try {
            return $value
                ? Attach::getUrlPath($path.'/'.$value)
                : $default;
        } catch (Exception) {
            return $default;
        }
    }

    protected function logo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (?string $value) {
            return $this->getImage($value, 'images', asset('images/agora-invoicing.png'));
        });
    }

    protected function adminLogo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (?string $value) {
            return $this->getImage($value, 'admin/images', asset('images/agora_admin_logo.png'));
        });
    }

    protected function favIcon(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (?string $value) {
            return $this->getImage($value, 'common/images', asset('images/faveo.png'));
        });
    }

    public function defaultCurrency()
    {
        return $this->belongsTo(Currency::class, 'default_currency', 'code');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country', 'country_code_char2');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state', 'state_subdivision_code');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'content', 'locale');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Common\Timezone, $this>
     */
    public function timezone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Timezone::class, 'timezone_id');
    }
}
