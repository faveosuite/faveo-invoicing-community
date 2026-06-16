<?php

namespace App\Model\Payment;

use Illuminate\Database\Eloquent\Model;
use Override;

class Period extends Model
{
    protected $table = 'periods';

    protected $fillable = ['name', 'days'];

    public function plans()
    {
        return $this->belongstoMany(Plan::class, 'plans_periods_relation')->withTimestamps();
    }

    #[Override]
    public function delete()
    {
        $this->plans()->detach();
    }
}
