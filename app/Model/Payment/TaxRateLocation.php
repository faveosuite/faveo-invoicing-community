<?php

declare(strict_types=1);

namespace App\Model\Payment;

use App\BaseModel;

/**
 * Postcode/city narrowing for a tax rate. location_type is 'postcode' or
 * 'city'; postcode codes may be wildcards/ranges (see TaxRateResolver).
 */
class TaxRateLocation extends BaseModel
{
    protected $table = 'tax_rate_locations';

    protected $fillable = ['tax_rate_id', 'location_code', 'location_type'];

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }
}
