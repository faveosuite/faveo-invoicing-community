<?php

namespace App\Model\Order;

use App\BaseModel;
use App\Model\Product\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends BaseModel
{
    use HasFactory;

    protected $table = 'invoice_items';

    protected $fillable = ['invoice_id', 'product_name',
        'regular_price', 'quantity', 'discount', 'tax_name',
        'tax_percentage', 'tax_code', 'tax_rate_id', 'discount_mode', 'subtotal', 'domain', 'plan_id', 'agents', 'billing_pay', 'product_id'];

    public function taxLines()
    {
        return $this->hasMany(\App\Model\Order\InvoiceTaxLine::class, 'invoice_item_id');
    }

    public function setDomainAttribute($value)
    {
        $this->attributes['domain'] = $this->get_domain($value);
    }

    public function get_domain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        if (! $domain) {
            $domain = $pieces['path'];
        }

        return strtolower($domain);
    }

    public function order()
    {
        return $this->hasOne(\App\Model\Order\Order::class, 'invoice_item_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
