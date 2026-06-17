<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Model\Common\Country;
use App\Model\Common\State;
use App\Traits\SystemActivityLogsTrait;
use Override;

/**
 * A generic tax rate (WooCommerce-style). See the create_tax_rates migration
 * for the semantics of priority / compound / tax_class.
 */
class TaxRate extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rates';

    protected $fillable = [
        'name', 'country', 'state', 'rate', 'priority',
        'compound', 'tax_class', 'display_order', 'active',
    ];

    protected $logName = 'tax';

    protected $logNameColumn = 'name';

    protected $logAttributes = [
        'name', 'country', 'state', 'rate', 'priority', 'compound', 'tax_class', 'active',
    ];

    protected $logUrl = [
        'segments' => ['tax', ':id', 'edit'],
    ];

    protected function getMappings(): array
    {
        return [
            'name' => ['Tax Name', fn ($value) => $value],
            'country' => [
                'Country',
                fn ($value) => $value
                    ? Country::where('country_code_char2', $value)->value('country_name')
                    : 'All Countries',
            ],
            'state' => [
                'State',
                fn ($value) => $value
                    ? State::where('iso2', $value)->value('state_subdivision_name')
                    : 'All States',
            ],
            'rate' => ['Tax Rate (%)', fn ($value) => $value],
            'priority' => ['Priority', fn ($value) => $value],
            'compound' => ['Is Compound Tax', fn ($value): string => $value ? 'Yes' : 'No'],
            'tax_class' => ['Tax Class', fn ($value) => $value ?: 'Standard'],
            'active' => ['Tax Status', fn ($value): array|string|null => $value ? __('message.active') : __('message.inactive')],
        ];
    }

    public function locations()
    {
        return $this->hasMany(TaxRateLocation::class, 'tax_rate_id');
    }

    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class', 'slug');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'priority' => 'integer',
            'compound' => 'boolean',
            'display_order' => 'integer',
            'active' => 'boolean',
        ];
    }
}
