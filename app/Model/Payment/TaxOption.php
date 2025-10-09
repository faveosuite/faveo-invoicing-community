<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class TaxOption extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rules';

    protected $fillable = ['tax_enable', 'inclusive', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'];

    protected $logName = 'taxes';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'tax_enable', 'inclusive', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no',
    ];

    protected $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'tax_enable' => ['Tax Enable', fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
            'inclusive' => ['Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'shop_inclusive' => ['Shop Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'cart_inclusive' => ['Cart Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'rounding' => ['Rounding', fn ($value) => $value === 1 ? 'Round per line' : 'Round total'],
            'Gst_no' => ['GST Number', fn ($value) => $value ?: 'N/A'],
            'cif_no' => ['CIF Number', fn ($value) => $value ?: 'N/A'],
        ];
    }
}
