<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $status_code
 * @property string $status_label
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status whereStatusCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status whereStatusLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Msg91Status whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Msg91Status extends Model
{
    /**
     * @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory>
     */
    use HasFactory;

    protected $fillable = ['status_code', 'status_label'];
}
