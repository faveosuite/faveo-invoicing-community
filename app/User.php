<?php

namespace App;

use App\Facades\Attach;
use App\Jobs\NotifyManagerChange;
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
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Laravel\Passport\Client;
use Laravel\Passport\HasApiTokens;
use Laravel\Passport\Token;
use Override;
use Spatie\Activitylog\Models\Activity;

// use Laravel\Cashier\Billable;
// use LinkThrow\Billing\CustomerBillableTrait;
// use App\Model\Common\Website;

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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
 * @property Carbon|null $deleted_at
 * @property string|null $gstin
 * @property int $is_tax_exempt
 * @property int $first_time_login
 * @property int $billing_pay_balance
 * @property-read User|null $accountManager
 * @property-read Collection<int, Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @property-read Collection<int, Auto_renewal> $auto_renewal
 * @property-read int|null $auto_renewal_count
 * @property-read Collection<int, Client> $clients
 * @property-read int|null $clients_count
 * @property-read Collection<int, Comment> $comments
 * @property-read int|null $comments_count
 * @property-read Country|null $countryRelation
 * @property-read Collection<int, ExportDetail> $export_details
 * @property-read int|null $export_details_count
 * @property-read string $full_name
 * @property-read Collection<int, Installation> $installations
 * @property-read int|null $installations_count
 * @property-read Collection<int, Invoice> $invoice
 * @property-read int|null $invoice_count
 * @property-read Collection<int, InvoiceItem> $invoiceItem
 * @property-read int|null $invoice_item_count
 * @property-read Collection<int, LicenseCallback> $licenseCallbacks
 * @property-read int|null $license_callbacks_count
 * @property-read Collection<int, LicenseReport> $licenseReports
 * @property-read int|null $license_reports_count
 * @property-read Collection<int, License> $licenses
 * @property-read int|null $licenses_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Client> $oauthApps
 * @property-read int|null $oauth_apps_count
 * @property-read Collection<int, Order> $order
 * @property-read int|null $order_count
 * @property-read Collection<int, OrderInvoiceRelation> $orderRelation
 * @property-read int|null $order_relation_count
 * @property-read Collection<int, Payment> $payment
 * @property-read int|null $payment_count
 * @property-read Collection<int, Subscription> $subscription
 * @property-read int|null $subscription_count
 * @property-read Timezone|null $timezone
 * @property-read Collection<int, Token> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, UserLinkReport> $userLinkReports
 * @property-read int|null $user_link_reports_count
 * @property-read Collection<int, VerificationAttempt> $verificationAttempts
 * @property-read int|null $verification_attempts_count
 * @property-read Collection<int, WhatsappIntegrationUser> $whatsappUsers
 * @property-read int|null $whatsapp_users_count
 *
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
 *
 * @mixin \Eloquent
 */
class User extends Model implements AuthenticatableContract, CanResetPasswordContract // @phpstan-ignore class.missingImplements
{
    use Authenticatable;

    use CanResetPassword;
    use HasApiTokens;
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use SystemActivityLogsTrait;

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

    public $timestamps = true;

    protected $table = 'users';

    protected $fillable = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'password', 'profile_pic',
        'address', 'country', 'currency', 'currency_symbol', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'skype', 'currency_symbol', 'referrer', 'google2fa_secret', 'is_2fa_enabled', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected string $logName = 'user';

    protected string $logNameColumn = 'user_name';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['clients', ':id'],
    ];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @return HasMany<Order, $this>
     */
    public function order(): HasMany
    {
        return $this->hasMany(Order::class, 'client');
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'updated_by_user_id');
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscription(): HasMany
    {
        // Return an Eloquent relationship.
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasManyThrough<InvoiceItem, Invoice, $this>
     */
    public function invoiceItem(): HasManyThrough
    {
        return $this->hasManyThrough(InvoiceItem::class, Invoice::class);
    }

    /**
     * @return HasManyThrough<OrderInvoiceRelation, Invoice, $this>
     */
    public function orderRelation(): HasManyThrough
    {
        return $this->hasManyThrough(OrderInvoiceRelation::class, Invoice::class);
    }

    /**
     * @return HasMany<Invoice, $this>
     */
    public function invoice(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * @return BelongsTo<Timezone, $this>
     */
    public function timezone(): BelongsTo
    {
        return $this->belongsTo(Timezone::class);
    }

    /**
     * @return HasMany<Auto_renewal, $this>
     */
    public function auto_renewal(): HasMany
    {
        return $this->hasMany(Auto_renewal::class, 'user_id');
    }

    /**
     * @return HasMany<ExportDetail, $this>
     */
    public function export_details(): HasMany
    {
        return $this->hasMany(ExportDetail::class, 'user_id');
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function profilePic(): Attribute
    {
        return Attribute::make(get: function (?string $value) {
            if ($value) {
                return Attach::getUrlPath('common/images/users/'.$value);
            }

            return Gravatar::get($this->attributes['email']);
        });
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function country(): Attribute
    {
        return Attribute::make(set: function ($value): array {
            $value = strtoupper((string) $value);

            return ['country' => $value];
        });
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function bussiness(): Attribute
    {
        return Attribute::make(get: function ($value) {
            $short = $this->attributes['bussiness'] ?? null;
            $name = '--';
            $bussiness = Bussiness::where('short', $short)->first();
            if ($bussiness) {
                return $bussiness->name;
            }

            return $name;
        });
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function companyType(): Attribute
    {
        return Attribute::make(get: function () {
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
     * @return BelongsTo<User, $this>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function accountManager(): BelongsTo
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
    public function save(array $options = []): bool
    {
        $changed = $this->isDirty() ? $this->getDirty() : false;
        $result = parent::save($options);
        $role = $this->role;
        if ($changed && $role == 'user' && emailSendingStatus()) {
            if (checkArray('manager', $changed) && $this->manager) {
                NotifyManagerChange::dispatch([$this->id], 'manager', (int) $this->getRawOriginal('manager'));
            }
            if (checkArray('account_manager', $changed) && $this->account_manager) {
                NotifyManagerChange::dispatch([$this->id], 'account_manager', (int) $this->getRawOriginal('account_manager'));
            }
        }

        return $result;
    }

    /**
     * @return array<mixed>
     */
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
            'active' => ['User active status', fn ($value): string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'profile_pic' => ['Profile picture', fn ($value) => $value],
            'address' => ['Address', fn ($value) => $value],
            'country' => ['Country', fn ($value) => $value],
            'currency' => ['Currency', fn ($value) => $value],
            'timezone_id' => ['Timezone', fn ($value) => Timezone::find($value)->name ?? $value],
            'mobile_code' => ['Mobile code', fn ($value) => $value],
            'bussiness' => ['Industry type', fn ($value) => $value],
            'company_type' => ['Company type', fn ($value) => $value],
            'company_size' => ['Company size', fn ($value) => match ($value) {
                '10001' => '10001+',
                default => $value,
            }],
            'ip' => ['IP address', fn ($value) => $value],
            'mobile_verified' => ['Mobile verified', fn ($value): string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'email_verified' => ['Email verified', fn ($value): string|array => $value === 1 ? trans('message.active') : trans('message.inactive')],
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

    /**
     * @return HasMany<UserLinkReport, $this>
     */
    public function userLinkReports(): HasMany
    {
        return $this->hasMany(UserLinkReport::class);
    }

    /**
     * @return Attribute<mixed, mixed>
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: function (): string {
            return sprintf('%s %s', $this->first_name, $this->last_name);
        });
    }

    /**
     * @return HasOne<Country, $this>
     */
    public function countryRelation(): HasOne
    {
        return $this->hasOne(Country::class, 'country_code_char2', 'country');
    }

    /**
     * @return HasMany<WhatsappIntegrationUser, $this>
     */
    public function whatsappUsers(): HasMany
    {
        return $this->hasMany(WhatsappIntegrationUser::class, 'user_id', 'id');
    }

    /**
     * @return HasMany<License, $this>
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(License::class, 'user_id');
    }

    /**
     * @return HasMany<Installation, $this>
     */
    public function installations(): HasMany
    {
        return $this->hasMany(Installation::class, 'user_id');
    }

    /**
     * @return HasMany<LicenseCallback, $this>
     */
    public function licenseCallbacks(): HasMany
    {
        return $this->hasMany(LicenseCallback::class, 'user_id');
    }

    /**
     * @return HasMany<LicenseReport, $this>
     */
    public function licenseReports(): HasMany
    {
        return $this->hasMany(LicenseReport::class, 'user_id');
    }
}
