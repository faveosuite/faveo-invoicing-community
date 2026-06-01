<?php

namespace App\Model\Payment;

use App\BaseModel;
use App\Traits\SystemActivityLogsTrait;

class TaxOption extends BaseModel
{
    use SystemActivityLogsTrait;

    protected $table = 'tax_rules';

    protected $fillable = ['tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'];

    protected $logName = 'tax';

    protected $logNameColumn = 'Settings';

    protected $logAttributes = [
        'tax_enable', 'inclusive', 'tax_based_on', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no',
    ];

    protected $requireLogUrl = false;

    protected function getMappings(): array
    {
        return [
            'tax_enable' => ['Tax Enable', fn ($value) => $value === 1 ? __('message.active') : __('message.inactive')],
            'inclusive' => ['Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'tax_based_on' => ['Calculate Tax Based On', fn ($value) => $value === 'base' ? 'Company address' : 'Billing address'],
            'shop_inclusive' => ['Shop Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'cart_inclusive' => ['Cart Prices Entered With Tax', fn ($value) => $value === 1 ? 'Yes' : 'No'],
            'rounding' => ['Round Tax To Whole Number', fn ($value) => $value === 1 ? __('message.enable') : __('message.disable')],
            'Gst_no' => ['GST Number', fn ($value) => $value ?: 'N/A'],
            'cif_no' => ['CIF Number', fn ($value) => $value ?: 'N/A'],
        ];
    }
}
