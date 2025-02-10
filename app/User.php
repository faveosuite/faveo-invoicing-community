<?php

namespace App;

use App\Facades\Attach;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

//use Laravel\Cashier\Billable;
//use LinkThrow\Billing\CustomerBillableTrait;
//use App\Model\Common\Website;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use HasFactory;
    use Authenticatable,
        CanResetPassword;
    // use LogsActivity;
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

    protected static $logName = 'User';

    protected static $logAttributes = ['first_name', 'last_name', 'user_name', 'company', 'zip',
        'state', 'town', 'mobile', 'mobile_country_iso',
        'email', 'role', 'active', 'profile_pic',
        'address', 'country', 'currency', 'timezone_id', 'mobile_code', 'bussiness',
        'company_type', 'company_size', 'ip', 'mobile_verified', 'email_verified', 'position', 'skype', 'manager', 'account_manager', 'google2fa_activation_date', 'backup_code', 'code_usage_count', 'gstin', ];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        $lastActivity = Activity::all()->last(); //returns the last logged activity
        // dd($lastActivity);
        if ($eventName == 'updated') {
            $this->enableLogging();

            return 'User  <strong> '.$this->first_name.' '.$this->last_name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'User <strong> '.$this->first_name.' '.$this->last_name.' </strong> was deleted';
        }

        return '';
    }

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

    public function assignSalesManager()
    {
        $managers = $this->where('role', 'admin')->where('position', 'manager')->pluck('id', 'first_name')->toArray();
        if (count($managers) > 0) {
            $randomized[] = array_rand($managers);
            shuffle($randomized);
            $manager = $managers[$randomized[0]];
        } else {
            $manager = '';
        }

        return $manager;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
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
