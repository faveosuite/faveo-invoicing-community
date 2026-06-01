<?php

namespace App\Model\Order;

use App\BaseModel;

/**
 * One applied tax (rate) against an invoice line item. Drives invoice tax
 * display and reporting without parsing label strings.
 */
class InvoiceTaxLine extends BaseModel
{
    protected $table = 'invoice_tax_lines';

    protected $fillable = [
        'invoice_id', 'invoice_item_id', 'tax_rate_id',
        'label', 'rate', 'compound', 'amount',
    ];

    protected $casts = [
        'rate' => 'float',
        'compound' => 'boolean',
        'amount' => 'float',
    ];

    public function invoice()
    {
        return $this->belongsTo(\App\Model\Order\Invoice::class, 'invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Model\Order\InvoiceItem::class, 'invoice_item_id');
    }
}
