<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;

/**
 * @property int $id
 * @property int $order_id
 * @property int $invoice_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInvoiceRelation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderInvoiceRelation extends BaseModel
{
    protected $table = 'order_invoice_relations';

    protected $fillable = ['order_id', 'invoice_id'];
}
