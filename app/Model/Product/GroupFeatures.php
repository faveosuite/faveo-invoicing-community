<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property string $features
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupFeatures whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class GroupFeatures extends BaseModel
{
    protected $table = 'group_features';

    protected $fillable = ['group_id', 'features'];
}
