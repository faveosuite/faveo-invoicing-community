<?php

declare(strict_types=1);

namespace App\BillingLog\Model;

use App\BaseModel;

/**
 * @property int $id
 * @property string $command
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $status
 * @property int|null $exception_log_id
 * @property int|null $duration
 * @property-read \App\BillingLog\Model\ExceptionLog|null $exception
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereCommand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereExceptionLogId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CronLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CronLog extends BaseModel
{
    protected $table = 'cron_logs';

    protected $fillable = ['command', 'description', 'status', 'exception_log_id', 'duration',
        'created_at',
    ];

    protected $hidden = ['exception_log_id'];

    public function exception()
    {
        return $this->belongsTo(ExceptionLog::class, 'exception_log_id');
    }
}
