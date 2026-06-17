<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\BaseModel;
use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends BaseModel
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = ['parent_id', 'invoice_id', 'amount',
        'payment_method', 'user_id', 'payment_status', 'created_at', 'amt_to_credit', 'currency', ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //    public function setCreatedAtAttribute($value) {
//        dd($value);
//    }
}
