<?php

namespace App;

use App\Facades\Attach;
use App\Http\Controllers\Auth\AuthController;
use App\License\Models\Installation;
use App\License\Models\License;
use App\License\Models\LicenseCallback;
use App\License\Models\LicenseReport;
use App\Model\Common\Bussiness;
use App\Model\Common\Country;
use App\Model\Common\Timezone;
use App\Model\Order\Invoice;
use App\Model\Order\InvoiceItem;
use App\Model\Order\Order;
use App\Model\Order\OrderInvoiceRelation;
use App\Model\Order\Payment;
use App\Model\Product\Subscription;
use App\Traits\SystemActivityLogsTrait;
use Cache;
use DB;
use Gravatar;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\HasApiTokens;
use Override;

//use Laravel\Cashier\Billable;
//use LinkThrow\Billing\CustomerBillableTrait;
//use App\Model\Common\Website;

/**
 * @property int $id
 * @property string $user_name
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $password
 * @property string $company
 * @property string|null $bussiness
 * @property string $mobile
 * @property string $mobile_code
 * @property string|null $mobile_country_iso
 * @property string $address
 * @property string $town
 * @property string|null $state
 * @property string $zip
 * @property string|null $profile_pic
 * @property int $active
 * @property string $role
 * @property string $currency
 * @property numeric|null $debit
 * @property int $timezone_id
 * @property string $language
 * @property string|null $remember_token
 * @property string|null $company_type
 * @property string|null $company_size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $country
 * @property string|null $ip
 * @property int $mobile_verified
 * @property int $email_verified
 * @property string|null $position
 * @property string|null $skype
 * @property User|null $manager
 * @property string|null $currency_symbol
 * @property string|null $account_manager
 * @property string|null $referrer
 * @property string|null $google2fa_secret
 * @property string|null $google2fa_activation_date
 * @property int $is_2fa_enabled
 * @property string|null $backup_code
 * @property int|null $code_usage_count
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $gstin
 * @property int $is_tax_exempt
 * @property int $first_time_login
 * @property int $billing_pay_balance
 * @property-read User|null $accountManager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Auto_renewal> $auto_renewal
 * @property-read int|null $auto_renewal_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $clients
 * @property-read int|null $clients_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read Country|null $countryRelation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\ExportDetail> $export_details
 * @property-read int|null $export_details_count
 * @property-read string $full_name
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Installation> $installations
 * @property-read int|null $installations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Invoice> $invoice
 * @property-read int|null $invoice_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InvoiceItem> $invoiceItem
 * @property-read int|null $invoice_item_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LicenseCallback> $licenseCallbacks
 * @property-read int|null $license_callbacks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, LicenseReport> $licenseReports
 * @property-read int|null $license_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, License> $licenses
 * @property-read int|null $licenses_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Client> $oauthApps
 * @property-read int|null $oauth_apps_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Order> $order
 * @property-read int|null $order_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrderInvoiceRelation> $orderRelation
 * @property-read int|null $order_relation_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Payment> $payment
 * @property-read int|null $payment_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Subscription> $subscription
 * @property-read int|null $subscription_count
 * @property-read Timezone|null $timezone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Passport\Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\UserLinkReport> $userLinkReports
 * @property-read int|null $user_link_reports_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\VerificationAttempt> $verificationAttempts
 * @property-read int|null $verification_attempts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\WhatsappIntegrationUser> $whatsappUsers
 * @property-read int|null $whatsapp_users_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAccountManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBackupCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillingPayBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBussiness($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCodeUsageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanySize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCurrencySymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDebit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstTimeLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogle2faActivationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGoogle2faSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereGstin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIs2faEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsTaxExempt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobileCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobileCountryIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMobileVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereProfilePic($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereSkype($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTimezoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTown($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUserName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasFactory;
    use Authenticatable;
    use CanResetPassword;
    use SystemActivityLogsTrait;
    use HasApiTokens;
    use Notifiable;
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;

    // use Billable;
    // use CustomerBillableTrait;

    #[Override]
    protected static function booted(): void
    {
        $clearCache = function (): void {
            foreach (['pagination_total_user', 'pagination_total_suspended_users'] as $key) {
                if (Cache::has($key)) {
                    Cache::forget($key);
                }
            }
        };
        static::created($clearCache);
        static::deleted($clearCache);       // suspend (soft delete)
        static::restored($clearCache);      // un-suspend
        static::forceDeleted($clearCache);  // permanent delete
    }

    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $timestamps = true;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'password', 'profile_pic',
        'address', 'country', 'currency', 'currency_symbol', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'skype', 'currency_symbol', 'referrer', 'google2fa_secret', 'is_2fa_enabled', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected string $logName = 'user';

    protected string $logNameColumn = 'user_name';

    protected array $logAttributes = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected array $logUrl = [
        'segments' => ['clients', ':id'],
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function order(): HasMany
    {
        return $this->hasMany(Order::class, 'client');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'updated_by_user_id');
    }

    public function subscription(): HasMany
    {
        // Return an Eloquent relationship.
        return $this->hasMany(Subscription::class);
    }

    public function invoiceItem(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(InvoiceItem::class, Invoice::class);
    }

    public function orderRelation(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(OrderInvoiceRelation::class, Invoice::class);
    }

    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Common\Timezone, $this>
     */
    public function timezone(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Timezone::class);
    }

    public function auto_renewal(): HasMany
    {
        return $this->hasMany(Auto_renewal::class, 'user_id');
    }

    public function export_details(): HasMany
    {
        return $this->hasMany(ExportDetail::class, 'user_id');
    }

    protected function profilePic(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (?string $value) {
            if ($value) {
                return Attach::getUrlPath('common/images/users/'.$value);
            }

            return Gravatar::get($this->attributes['email']);
        });
    }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function country(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(set: function ($value): array {
            $value = strtoupper((string) $value);

            return ['country' => $value];
        });
    }

    protected function bussiness(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function ($value) {
            $short = $this->attributes['bussiness'] ?? null;
            $name = '--';
            $bussiness = Bussiness::where('short', $short)->first();
            if ($bussiness) {
                return $bussiness->name;
            }

            return $name;
        });
    }

    protected function companyType(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function () {
            $short = $this->attributes['company_type'] ?? null;
            $name = '--';
            $company = DB::table('company_types')->where('short', $short)->first();
            if ($company) {
                return $company->name;
            }

            return $name;
        });
    }

    // public function forceDelete()
    // {
    //     $this->invoiceItem()->delete();
    //     $this->orderRelation()->delete();
    //     $this->invoice()->delete();
    //     $this->order()->delete();
    //     $this->subscription()->delete();
    //     $this->comments()->delete();
    //     return parent::delete();
    // }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\User, $this>
     */
    public function accountManager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'account_manager');
    }

    public function assignManagerByPosition(string $position): ?int
    {
        return $this->where('role', 'admin')
            ->where('position', $position)
            ->inRandomOrder()
            ->value('id');
    }

    #[Override]
    public function save(array $options = []): void
    {
        $changed = $this->isDirty() ? $this->getDirty() : false;
        parent::save($options);
        $role = $this->role;
        if ($changed && checkArray('manager', $changed) && $role == 'user' && emailSendingStatus()) {
            $auth = new AuthController();
            $auth->salesManagerMail($this);
        }

        if ($changed && checkArray('account_manager', $changed) && $role == 'user' && emailSendingStatus()) {
            $auth = new AuthController();
            $auth->accountManagerMail($this);
        }
    }

    protected function getMappings(): array
    {
        return [
            'first_name' => ['First name', strip_tags(...)],
            'last_name' => ['Last name', strip_tags(...)],
            'user_name' => ['User name', fn ($value) => $value],
            'company' => ['Company', fn ($value) => $value],
            'zip' => ['ZIP code', fn ($value) => $value],
            'state' => ['State', fn ($value) => $value],
            'town' => ['Town', fn ($value) => $value],
            'mobile' => ['Mobile', fn ($value) => $value],
            'mobile_country_iso' => ['Mobile country ISO', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'role' => ['Role', ucfirst(...)],
            'active' => ['User active status', fn ($value): \Illuminate\Contracts\Translation\Translator|string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'profile_pic' => ['Profile picture', fn ($value) => $value],
            'address' => ['Address', fn ($value) => $value],
            'country' => ['Country', fn ($value) => $value],
            'currency' => ['Currency', fn ($value) => $value],
            'timezone_id' => ['Timezone', fn ($value) => Timezone::find($value)?->name ?? $value],
            'mobile_code' => ['Mobile code', fn ($value) => $value],
            'bussiness' => ['Industry type', fn ($value) => $value],
            'company_type' => ['Company type', fn ($value) => $value],
            'company_size' => ['Company size', fn ($value) => match ($value) {
                '10001' => '10001+',
                default => $value,
            }],
            'ip' => ['IP address', fn ($value) => $value],
            'mobile_verified' => ['Mobile verified', fn ($value): \Illuminate\Contracts\Translation\Translator|string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'email_verified' => ['Email verified', fn ($value): \Illuminate\Contracts\Translation\Translator|string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'position' => [
                'Position',
                fn ($value): ?string => match ($value) {
                    'account_manager' => 'Account Manager',
                    'manager' => 'Sales Manager',
                    default => null,
                },
            ],
            'skype' => ['Skype', fn ($value) => $value],
            'google2fa_activation_date' => ['2FA activation date', fn ($value) => Date::parse($value)->toDateTimeString()],
            'code_usage_count' => ['Code usage count', fn ($value) => $value],
            'language' => ['Language', fn ($value) => $value],
        ];
    }

    /**
     * @return HasMany<VerificationAttempt, $this>
     */
    public function verificationAttempts(): HasMany
    {
        return $this->hasMany(VerificationAttempt::class);
    }

    public function userLinkReports(): HasMany
    {
        return $this->hasMany(UserLinkReport::class);
    }

    protected function fullName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): string {
            return sprintf('%s %s', $this->first_name, $this->last_name);
        });
    }

    public function countryRelation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Country::class, 'country_code_char2', 'country');
    }

    public function whatsappUsers(): HasMany
    {
        return $this->hasMany(WhatsappIntegrationUser::class, 'user_id', 'id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'user_id');
    }

    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'user_id');
    }

    public function licenseCallbacks(): HasMany
    {
        return $this->hasMany(LicenseCallback::class, 'user_id');
    }

    public function licenseReports(): HasMany
    {
        return $this->hasMany(LicenseReport::class, 'user_id');
    }
}
