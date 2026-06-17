<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $group_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PipedriveGroups whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PipedriveGroups extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_name',
    ];
}
