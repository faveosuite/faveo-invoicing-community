<?php

namespace App\Model\Payment;

use App\BaseModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TaxOption extends BaseModel
{
    use LogsActivity;

    protected $table = 'tax_rules';

    protected $fillable = ['tax_enable', 'inclusive', 'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', 'cif_no'];

    protected static $logName = 'Tax Class';

    protected static $logAttributes = ['tax_enable', 'inclusive',
        'shop_inclusive', 'cart_inclusive', 'rounding', 'Gst_no', ];

    protected static $logOnlyDirty = true;

    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created') {
            return 'Tax Rule   was created';
        }

        if ($eventName == 'updated') {
            return 'Tax Rule was updated';
        }

        if ($eventName == 'deleted') {
            return 'Tax Rule  was deleted';
        }

        return '';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
