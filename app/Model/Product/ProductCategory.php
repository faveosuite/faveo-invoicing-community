<?php

namespace App\Model\Product;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductCategory extends Model
{
    use LogsActivity;

    protected $table = 'product_categories';

    protected $fillable = ['id', 'category_name'];

    protected static $logName = 'Product Category';

    protected static $logAttributes = ['category_name'];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        // dd(Activity::where('subject_id',)->pluck('subject_id'));
        if ($eventName == 'created') {
            return 'Product Category'.$this->name.' was created';
        }

        if ($eventName == 'updated') {
            return 'Product Category  <strong> '.$this->name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Product Category <strong> '.$this->name.' </strong> was deleted';
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
