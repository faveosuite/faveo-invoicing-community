<?php

namespace App\BillingLog\Model;

use Illuminate\Database\Eloquent\Model;

class LogCategory extends Model
{
    protected $table = 'log_categories';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function exceptions()
    {
        return $this->hasMany(ExceptionLog::class);
    }

    public function mail()
    {
        return $this->hasMany(MailLog::class);
    }
}
