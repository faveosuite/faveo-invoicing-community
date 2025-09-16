<?php

namespace App\BillingLog\Model;

use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model
{
    protected $table = 'exception_logs';

    protected $fillable = ['log_category_id', 'file', 'line', 'trace', 'message',
        'created_at'
    ];

    public function category()
    {
        return $this->belongsTo(LogCategory::class, 'log_category_id');
    }
}
