<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    protected $table = 'periods';

    protected $fillable = ['name', 'days'];

    public function plans()
    {
        return $this->belongstoMany(\App\Model\Payment\Plan::class, 'plans_periods_relation')->withTimestamps();
    }

    public function delete()
    {
        $this->plans()->detach();
    }
}
