<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxClass extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_classes';

    protected $fillable = ['name'];


    protected $logName = 'taxes';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'name'
    ];

    protected $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Class Name', fn ($value) => $value],
        ];
    }


    public function tax()
    {
        return $this->hasMany(\App\Model\Payment\Tax::class, 'tax_classes_id');
    }

    public function tax_product_relation()
    {
        return $this->hasMany(\App\Model\Payment\TaxProductRelation::class, 'tax_class_id');
    }
}
