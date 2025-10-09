<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class Currency extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'currencies';

    protected $fillable = ['code', 'symbol', 'name', 'status'];


    protected $logName = 'currency';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'code', 'symbol', 'name', 'status'
    ];

    protected $logUrl = ['currency'];

    protected function getMappings(): array
    {
        return [
            'code' => ['Currency Code', fn ($value) => $value],
            'symbol' => ['Currency Symbol', fn ($value) => $value],
            'name' => ['Currency Name', fn ($value) => $value],
            'status' => ["{$this->name} currency status", fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
        ];
    }

    public function country()
    {
        return $this->hasMany(\App\Model\Common\Country::class, 'currency_id');
    }
}
