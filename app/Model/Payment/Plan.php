<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Configure\ConfigOption;
use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends BaseModel
{
    use HasFactory;
    use SystemActivityLogsTrait;

    protected $table = 'plans';

    protected $fillable = ['name', 'product', 'allow_tax', 'days'];

    protected $logName = 'plan';

    protected $logNameColumn = 'name';


    protected $logAttributes = [
        'name', 'product', 'allow_tax', 'days'
    ];

    protected $logUrl = ['plans','edit'];

    protected function getMappings(): array
    {
        return [
            'name' => ['Plan Name', fn ($value) => $value],
            'product' => ['Product', fn ($value) => \App\Model\Product\Product::find($value)?->name],
            'allow_tax' => ['Allow Tax', fn ($value) => $value === 1 ? __('message.yes') : __('message.no')],
            'days' => ['Plan Days', fn ($value) => $value],
        ];
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
        parent::delete();
        $this->planPrice()->delete();
    }

    public function configOptions()
    {
        return $this->hasMany(ConfigOption::class);
    }
}
