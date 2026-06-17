<?php

declare(strict_types=1);

namespace App;

use App\Model\Order\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_log extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $table = 'payment_logs';

    protected $fillable = ['id', 'from', 'to', 'date', 'subject', 'body', 'status', 'amount', 'payment_type'];

    public function user()
    {
        return $this->belongsTo(User::class, 'from', 'email');
    }

    public function orderDetails()
    {
        return $this->belongsTo(Order::class, 'order', 'number');
    }
}
