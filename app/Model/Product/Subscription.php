<?php

namespace App\Model\Product;

use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subscription extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'subscriptions';

    protected $fillable = ['name', 'description', 'days', 'ends_at', 'update_ends_at',
        'user_id', 'plan_id', 'order_id', 'deny_after_subscription', 'version', 'product_id', 'support_ends_at', 'version_updated_at', 'is_subscribed','is_deleted' ];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    protected static $logName = 'Subscription';

    protected static $logAttributes = ['ends_at', 'update_ends_at', 'support_ends_at', 'user_id', 'plan_id', 'order_id',  'version', 'product_id', 'version_updated_at'];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            // dd($this->user()->get()->toArray(), 'cxzc');
            return 'Subscription for User_Id   <strong> '.$this->user_id.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Subscription for User <strong> '.$this->user_id.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Subscription for User <strong> '.$this->user_id.' </strong> was deleted';
        }

        return '';
    }

    public function plan()
    {
        return $this->belongsTo(\App\Model\Payment\Plan::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Model\Product\Product::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function order()
    {
        return $this->belongsTo(\App\Model\Order\Order::class);
    }

    // public function getEndsAtAttribute($value)
    // {
    //      $date1 = new DateTime($value);
    //     $tz = \Auth::user()->timezone()->first()->name;
    //     $date1->setTimezone(new DateTimeZone($tz));
    //     $date = $date1->format('M j, Y, g:i a ');

    //     return $date;
    // }

    //    public function delete() {
//
//
//        $this->Plan()->delete();
//
//
//        return parent::delete();
//    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
