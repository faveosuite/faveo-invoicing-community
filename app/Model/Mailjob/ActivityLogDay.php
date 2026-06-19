<?php

declare(strict_types=1);

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $days
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLogDay whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ActivityLogDay extends Model
{
    protected $table = 'activity_log_days';

    protected $fillable = ['days'];
}
