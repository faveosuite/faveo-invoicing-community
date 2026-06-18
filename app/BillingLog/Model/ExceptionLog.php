<?php

declare(strict_types=1);

namespace App\BillingLog\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $log_category_id
 * @property string $file
 * @property int $line
 * @property string $trace
 * @property string $message
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\BillingLog\Model\LogCategory|null $category
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereLogCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereTrace($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExceptionLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExceptionLog extends Model
{
    protected $table = 'exception_logs';

    protected $fillable = ['log_category_id', 'file', 'line', 'trace', 'message',
        'created_at',
    ];

    public function category()
    {
        return $this->belongsTo(LogCategory::class, 'log_category_id');
    }
}
