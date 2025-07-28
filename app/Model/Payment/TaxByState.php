<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;

class TaxByState extends Model
{
    protected $table = 'tax_by_states';

    protected $fillable = ['country', 'state_code', 'state', 'c_gst', 's_gst', 'i_gst', 'ut_gst'];
}
