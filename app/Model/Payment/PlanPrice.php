<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PlanPrice extends Model
{
    use HasFactory,LogsActivity;

    protected $table = 'plan_prices';

    protected $fillable = ['plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'country_id', 'offer_price'];

    protected static $logName = 'Plan Price';

    protected static $logAttributes = ['plan_id', 'currency', 'add_price', 'renew_price', 'price_description', 'product_quantity', 'no_of_agents', 'offer_price'];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Plan Price with Id <strong> '.$this->plan_id.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Plan Price with Id<strong> '.$this->plan_id.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Plan Price with Id<strong> '.$this->plan_id.' </strong> was deleted';
        }

        return '';

        // return "Product  has been {$eventName}";
        // \Auth::user()->activity;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
