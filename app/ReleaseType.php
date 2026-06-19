<?php

declare(strict_types=1);

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $value
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReleaseType whereValue($value)
 * @mixin \Eloquent
 */
class ReleaseType extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    public $timestamps = true;

    protected $table = 'release_types';

    protected $fillable = ['type'];
}
