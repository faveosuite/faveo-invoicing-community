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

    protected $logName = 'user';

    protected $logNameColumn = 'user_name';

    protected $logAttributes = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected $logUrl = [
        'segments' => ['clients', ':id'],
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function order()
    {
        return $this->hasMany(Order::class, 'client');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'updated_by_user_id');
    }

    public function subscription()
    {
        // Return an Eloquent relationship.
        return $this->hasMany(Subscription::class);
    }

    public function invoiceItem()
    {
        return $this->hasManyThrough(InvoiceItem::class, Invoice::class);
    }

    public function orderRelation()
    {
        return $this->hasManyThrough(OrderInvoiceRelation::class, Invoice::class);
    }

    public function invoice()
    {
        return $this->hasMany(Invoice::class);
    }

    public function timezone()
    {
        return $this->belongsTo(Timezone::class);
    }

    public function auto_renewal()
    {
        return $this->hasMany(Auto_renewal::class, 'user_id');
    }

    public function export_details()
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

    public function payment()
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

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager');
    }

    public function accountManager()
    {
        return $this->belongsTo(User::class, 'account_manager');
    }

    public function assignManagerByPosition(string $position)
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

    public function userLinkReports()
    {
        return $this->hasMany(UserLinkReport::class);
    }

    protected function fullName(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(get: function (): string {
            return sprintf('%s %s', $this->first_name, $this->last_name);
        });
    }

    public function countryRelation()
    {
        return $this->hasOne(Country::class, 'country_code_char2', 'country');
    }

    public function whatsappUsers()
    {
        return $this->hasMany(WhatsappIntegrationUser::class, 'user_id', 'id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class, 'user_id');
    }

    public function installations()
    {
        return $this->hasMany(Installation::class, 'user_id');
    }

    public function licenseCallbacks()
    {
        return $this->hasMany(LicenseCallback::class, 'user_id');
    }

    public function licenseReports()
    {
        return $this->hasMany(LicenseReport::class, 'user_id');
    }
}
