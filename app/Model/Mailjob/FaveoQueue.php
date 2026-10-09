<?php

declare(strict_types=1);

namespace App\Model\Mailjob;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $service_id
 * @property string $key
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FaveoQueue whereValue($value)
 *
 * @mixin \Eloquent
 */
class FaveoQueue extends Model
{
    protected $table = 'faveo_queues';

    protected $fillable = ['key', 'value', 'service_id'];
}
