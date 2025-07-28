<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_log extends Model
{
    use HasFactory;

    protected $table = 'payment_logs';

    protected $fillable = ['id', 'from', 'to', 'date', 'subject', 'body', 'status', 'created_at', 'amount', 'payment_type'];
}
