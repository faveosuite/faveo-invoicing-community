<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auto_renewal extends Model
{
    use HasFactory;

    protected $table = 'auto_renewals';

    protected $fillable = ['user_id', 'customer_id', 'order_id', 'payment_method', 'payment_intent_id'];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
