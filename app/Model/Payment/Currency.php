<?php

namespace App\Model\Payment;

use App\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Currency extends BaseModel
{
    use LogsActivity;

    protected $table = 'currencies';

    protected $fillable = ['code', 'symbol', 'name', 'status', 'id', 'dashboard_currency'];

    protected static $logName = 'Currency';

    protected static $logAttributes = ['code', 'symbol', 'name', 'status'];

    protected static $logOnlyDirty = true;

    protected static function booted(): void
    {
        static::addGlobalScope('active_country', function (Builder $builder) {
            $builder->whereHas('country', function ($query) {
                $query->where('status', true);
            });
        });
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        // dd(Activity::where('subject_id',)->pluck('subject_id'));
        if ($eventName == 'created') {
            return 'Currency  <strong> '.$this->name.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Currency <strong> '.$this->name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Currency <strong> '.$this->name.' </strong> was deleted';
        }

        return '';

        // return "Product  has been {$eventName}";
        // \Auth::user()->activity;
    }

    public function country()
    {
        return $this->hasMany(\App\Model\Common\Country::class, 'currency_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
