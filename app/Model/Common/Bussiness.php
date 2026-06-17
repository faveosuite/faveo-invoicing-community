<?php

declare(strict_types=1);

namespace App\Model\Common;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $name
 * @property string|null $short
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness whereShort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bussiness whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Bussiness extends Model
{
    protected $table = 'bussinesses';

    protected $fillable = ['short', 'name'];
}
