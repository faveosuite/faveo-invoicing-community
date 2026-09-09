<?php

declare(strict_types=1);

namespace App\Model;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $path
 * @property int $status
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plugin whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Plugin extends BaseModel
{
    protected $table = 'plugins';

    protected $fillable = ['name', 'path', 'status', 'type'];
}
