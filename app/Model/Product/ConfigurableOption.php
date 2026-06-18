<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\BaseModel;

/**
 * @property int $id
 * @property int $group_id
 * @property int $type
 * @property string $title
 * @property string $options
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigurableOption whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ConfigurableOption extends BaseModel
{
    protected $table = 'configurable_options';

    protected $fillable = ['group_id', 'type', 'title', 'options', 'price'];
}
