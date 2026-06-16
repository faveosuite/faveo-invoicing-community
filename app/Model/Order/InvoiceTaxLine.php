<?php

namespace App\Model\Order;

use App\BaseModel;
use Override;

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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(InvoiceItem::class, 'invoice_item_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'rate' => 'float',
            'compound' => 'boolean',
            'amount' => 'float',
        ];
    }
}
