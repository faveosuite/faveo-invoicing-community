<?php

declare(strict_types=1);

namespace App\Model\Configure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $option_id
 * @property string $key
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\Configure\ConfigOption $configOption
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereOptionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigOptionValue whereValue($value)
 *
 * @mixin \Eloquent
 */
class ConfigOptionValue extends Model
{
    use HasFactory;

    protected $table = 'config_option_values';

    protected $guarded = [];

    // Define the relationship with ConfigOption
    public function configOption(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ConfigOption::class, 'option_id');
    }
}
