<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class Tax extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'taxes';

    protected $fillable = ['level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound'];

    protected $logName = 'taxes';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound',
    ];

    protected $logUrl = ['tax', 'edit'];

    protected function getMappings(): array
    {
        return [
            'level' => ['Tax Level', fn ($value) => $value === 1 ? 'Country' : ($value === 2 ? 'State' : 'City')],
            'name' => ['Tax Name', fn ($value) => $value],
            'country' => ['Country', fn ($value) => \App\Model\Common\Country::find($value)?->nicename],
            'state' => ['State', fn ($value) => $value ? \App\Model\Common\State::find($value)?->name : 'All States'],
            'rate' => ['Tax Rate (%)', fn ($value) => $value],
            'active' => ["{$this->name} tax status", fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
            'tax_classes_id' => ['Tax Class', fn ($value) => $value ? \App\Model\Payment\TaxClass::find($value)?->name : 'No Class'],
            'compound' => ['Is Compound Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
        ];
    }

    public function taxClass()
    {
        return $this->belongsTo(\App\Model\Payment\TaxClass::class, 'tax_classes_id');
    }
}
