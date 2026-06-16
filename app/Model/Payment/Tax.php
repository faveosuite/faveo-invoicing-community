<?php

namespace App\Model\Payment;

use App\Model\Common\Country;
use App\Model\Common\State;
use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class Tax extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'taxes';

    protected $fillable = ['level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound'];

    protected $logName = 'tax';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'level', 'name', 'country', 'state', 'rate', 'active', 'tax_classes_id', 'compound',
    ];

    protected $logUrl = [
        'segments' => ['tax', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'level' => ['Tax Level', fn ($value) => $value === 1 ? 'Country' : ($value === 2 ? 'State' : 'City')],
            'name' => ['Tax Name', fn ($value) => $value],
            'country' => ['Country', fn ($value) => Country::where('country_code_char2', $value)->value('country_name')],
            'state' => [
                'State',
                fn ($value) => $value
                    ? State::where('iso2', $value)->value('state_subdivision_name')
                    : 'All States',
            ],
            'rate' => ['Tax Rate (%)', fn ($value) => $value],
            'active' => ["{$this->name} tax status", fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
            'tax_classes_id' => ['Tax Class', fn ($value) => $value ? TaxClass::find($value)?->name : 'No Class'],
            'compound' => ['Is Compound Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
        ];
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class, 'tax_classes_id');
    }
}
