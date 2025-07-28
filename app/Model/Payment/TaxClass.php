<?php

namespace App\Model\Payment;

use App\BaseModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxClass extends BaseModel
{
    use LogsActivity;

    protected $table = 'tax_classes';

    protected $fillable = ['name'];

    protected static $logName = 'Tax Class';

    protected static $logAttributes = ['name'];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Tax Class  <strong> '.$this->name.' </strong> was created';
        }

        if ($eventName == 'updated') {
            return 'Tax Class<strong> '.$this->name.'</strong> was updated';
        }

        if ($eventName == 'deleted') {
            return 'Tax Class<strong> '.$this->name.' </strong> was deleted';
        }

        return '';
    }

    public function tax()
    {
        return $this->hasMany(\App\Model\Payment\Tax::class, 'tax_classes_id');
    }

    public function tax_product_relation()
    {
        return $this->hasMany(\App\Model\Payment\TaxProductRelation::class, 'tax_class_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
