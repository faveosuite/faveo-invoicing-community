<?php

namespace App\Model\Order;

use App\BaseModel;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $invoice_id
 * @property string $product_name
 * @property int|null $product_id
 * @property string $regular_price
 * @property string $quantity
 * @property string $discount
 * @property string $tax_name
 * @property string $tax_percentage
 * @property string $tax_code
 * @property int|null $tax_rate_id
 * @property string $discount_mode
 * @property string $subtotal
 * @property string $domain
 * @property int|null $plan_id
 * @property string|null $agents
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $billing_pay
 * @property-read \App\Model\Order\Invoice|null $invoice
 * @property-read \App\Model\Order\Order|null $order
 * @property-read Product|null $product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Order\InvoiceTaxLine> $taxLines
 * @property-read int|null $tax_lines_count
 *
 * @method static \Database\Factories\Model\Order\InvoiceItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereAgents($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereBillingPay($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDiscountMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereRegularPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereTaxRateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceItem whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class InvoiceItem extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = ['invoice_id', 'product_name',
        'regular_price', 'quantity', 'discount', 'tax_name',
        'tax_percentage', 'tax_code', 'tax_rate_id', 'discount_mode', 'subtotal', 'domain', 'plan_id', 'agents', 'billing_pay', 'product_id'];

    public function taxLines(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceTaxLine::class, 'invoice_item_id');
    }

    protected function domain(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(set: function ($value): array {
            return ['domain' => $this->get_domain($value)];
        });
    }

    public function get_domain(mixed $url): string
    {
        $pieces = parse_url((string) $url);
        $domain = $pieces['host'] ?? '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        if (! $domain) {
            $domain = $pieces['path'];
        }

        return strtolower($domain);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<\App\Model\Order\Order, $this>
     */
    public function order(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Order::class, 'invoice_item_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Model\Order\Invoice, $this>
     */
    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
