<?php

namespace App;

use App\Facades\Attach;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

//use Laravel\Cashier\Billable;
//use LinkThrow\Billing\CustomerBillableTrait;
//use App\Model\Common\Website;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasFactory;
    use Authenticatable,
        CanResetPassword;
    use SystemActivityLogsTrait;
    use SoftDeletes;

    // use Billable;
    // use CustomerBillableTrait;

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
        'email', 'password', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'currency_symbol', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'manager', 'currency_symbol', 'account_manager', 'referrer', 'google2fa_secret', 'is_2fa_enabled', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected $logName = 'user';
    protected $logNameColumn = 'user_name';

    protected $logAttributes = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'manager', 'account_manager', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', 'language'];

    protected $logUrl = ['/clients'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function order()
    {
        return $this->hasMany(\App\Model\Order\Order::class, 'client');
    }

    public function comments()
    {
        return $this->hasMany(\App\Comment::class, 'updated_by_user_id');
    }

    public function subscription()
    {
        // Return an Eloquent relationship.
        return $this->hasMany(\App\Model\Product\Subscription::class);
    }

    public function invoiceItem()
    {
        return $this->hasManyThrough(\App\Model\Order\InvoiceItem::class, \App\Model\Order\Invoice::class);
    }

    public function orderRelation()
    {
        return $this->hasManyThrough(\App\Model\Order\OrderInvoiceRelation::class, \App\Model\Order\Invoice::class);
    }

    public function invoice()
    {
        return $this->hasMany(\App\Model\Order\Invoice::class);
    }

    public function timezone()
    {
        return $this->belongsTo(\App\Model\Common\Timezone::class);
    }

    public function auto_renewal()
    {
        return $this->hasMany(\App\Auto_renewal::class, 'user_id');
    }

    public function export_details()
    {
        return $this->hasMany(\App\ExportDetail::class, 'user_id');
    }

    // public function getCreatedAtAttribute($value)
    // {
    //     if (\Auth::user()) {
    //         $tz = \Auth::user()->timezone()->first()->name;
    //         $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'UTC');

    //         return $date->setTimezone($tz);
    //     }

    //     return $value;
    // }

    public function getProfilePicAttribute($value)
    {
        $image = \Gravatar::get($this->attributes['email']);

        if ($value) {
            $image = Attach::getUrlPath('common/images/users/'.$value);
        }

        return $image;
    }

    public function payment()
    {
        return $this->hasMany(\App\Model\Order\Payment::class);
    }

    public function setCountryAttribute($value)
    {
        $value = strtoupper($value);
        $this->attributes['country'] = $value;
    }

    public function getBussinessAttribute($value)
    {
        $short = $this->attributes['bussiness'];
        $name = '--';
        $bussiness = \App\Model\Common\Bussiness::where('short', $short)->first();
        if ($bussiness) {
            $name = $bussiness->name;
        }

        return $name;
    }

    public function getCompanyTypeAttribute()
    {
        $short = $this->attributes['company_type'];
        $name = '--';
        $company = \DB::table('company_types')->where('short', $short)->first();
        if ($company) {
            $name = $company->name;
        }

        return $name;
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
        return $this->belongsTo(\App\User::class, 'manager');
    }

    public function accountManager()
    {
        return $this->belongsTo(\App\User::class, 'account_manager');
    }

    public function assignManagerByPosition(string $position)
    {
        return $this->where('role', 'admin')
            ->where('position', $position)
            ->inRandomOrder()
            ->value('id');
    }

    public function save(array $options = [])
    {
        $changed = $this->isDirty() ? $this->getDirty() : false;
        parent::save($options);
        $role = $this->role;
        if ($changed && checkArray('manager', $changed) && $role == 'user' && emailSendingStatus()) {
            $auth = new Http\Controllers\Auth\AuthController();
            $auth->salesManagerMail($this);
        }

        if ($changed && checkArray('account_manager', $changed) && $role == 'user' && emailSendingStatus()) {
            $auth = new Http\Controllers\Auth\AuthController();
            $auth->accountManagerMail($this);
        }
    }

    protected function getMappings(): array
    {
        return [
            'first_name' => ['First name', fn ($value) => strip_tags($value)],
            'last_name' => ['Last name', fn ($value) => strip_tags($value)],
            'user_name' => ['User name', fn ($value) => $value],
            'company' => ['Company', fn ($value) => $value],
            'zip' => ['ZIP code', fn ($value) => $value],
            'state' => ['State', fn ($value) => $value],
            'town' => ['Town', fn ($value) => $value],
            'mobile' => ['Mobile', fn ($value) => $value],
            'mobile_country_iso' => ['Mobile country ISO', fn ($value) => $value],
            'email' => ['Email', fn ($value) => $value],
            'role' => ['Role', fn ($value) => ucfirst($value)],
            'active' => ['User active status', fn ($value) => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'profile_pic' => ['Profile picture', fn ($value) => $value],
            'address' => ['Address', fn ($value) => $value],
            'country' => ['Country', fn ($value) => $value],
            'currency' => ['Currency', fn ($value) => $value],
            'timezone_id' => ['Timezone', fn ($value) => $value],
            'mobile_code' => ['Mobile code', fn ($value) => $value],
            'bussiness' => ['Business', fn ($value) => $value],
            'company_type' => ['Company type', fn ($value) => $value],
            'company_size' => ['Company size', fn ($value) => $value],
            'ip' => ['IP address', fn ($value) => $value],
            'mobile_verified' => ['Mobile verified', fn ($value) => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'email_verified' => ['Email verified', fn ($value) => $value === 1 ? trans('message.active') : trans('message.inactive')],
            'position' => ['Position', fn ($value) => $value],
            'skype' => ['Skype', fn ($value) => $value],
            'manager' => ['Manager', fn ($value) => $value],
            'account_manager' => ['Account manager', fn ($value) => $value],
            'google2fa_activation_date' => ['2FA activation date', fn ($value) => $value],
            'backup_code' => ['Backup code', fn ($value) => $value],
            'code_usage_count' => ['Code usage count', fn ($value) => $value],
            'language' => ['Language', fn ($value) => $value],
        ];
    }

    public function verificationAttempts(): HasMany
    {
        return $this->hasMany(VerificationAttempt::class);
    }

    public function userLinkReports()
    {
        return $this->hasMany(UserLinkReport::class);
    }
}
