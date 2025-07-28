<?php

namespace App\Model\Order;

use App\BaseModel;

class OrderInvoiceRelation extends BaseModel
{
    protected $table = 'order_invoice_relations';

    protected $fillable = ['order_id', 'invoice_id'];
}
