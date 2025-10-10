<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Configure\ConfigOption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Plan extends BaseModel
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'plans';

    protected $fillable = ['name', 'product', 'allow_tax', 'days'];

    protected static $logName = 'Plans';

    protected static $logAttributes = ['name', 'product', 'days'];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Plan  <strong> '.$this->name.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Plan <strong> '.$this->name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Plan <strong> '.$this->name.' </strong> was deleted';
        }

        return '';

        // return "Product  has been {$eventName}";
        // \Auth::user()->activity;
    }

    public function planPrice()
    {
        return $this->hasMany(\App\Model\Payment\PlanPrice::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Model\Product\Product::class, 'product', 'id');
    }

    public function periods()
    {
        return $this->belongstoMany(\App\Model\Payment\Period::class, 'plans_periods_relation')->withTimestamps();
    }

    public function delete()
    {
        return \DB::transaction(function () {
            $this->planPrice()->delete();

            return parent::delete();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function configOptions()
    {
        return $this->hasMany(ConfigOption::class);
    }
}
