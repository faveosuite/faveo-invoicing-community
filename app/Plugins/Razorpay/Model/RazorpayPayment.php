<?php

namespace App\Plugins\Razorpay\Model;

use Illuminate\Database\Eloquent\Model;

class RazorpayPayment extends Model
{
    protected $table = 'razorpay';

    protected $fillable = ['image_url', 'processing_fee', 'base_currency', 'supported_currencies'];
}
