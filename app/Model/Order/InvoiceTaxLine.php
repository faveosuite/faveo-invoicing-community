<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use Override;

/**
 * One applied tax (rate) against an invoice line item. Drives invoice tax
 * display and reporting without parsing label strings.
 *
 * @property int $id
 * @property int $invoice_id
 * @property int|null $invoice_item_id
 * @property int|null $tax_rate_id
 * @property string $label
 * @property float $rate
 * @property bool $compound
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Order\Invoice|null $invoice
 * @property-read \App\Model\Order\InvoiceItem|null $item
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereCompound($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereInvoiceItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereTaxRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTaxLine whereUpdatedAt($value)
 *
 * @mixin \Eloquent
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
