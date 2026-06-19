<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Invoice|null $invoice
 * @property-read InvoiceItem|null $item
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

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    /**
     * @return BelongsTo<InvoiceItem, $this>
     */
    public function item(): BelongsTo
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
