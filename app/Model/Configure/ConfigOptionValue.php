<?php

declare(strict_types=1);

namespace App\Model\Configure;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $option_id
 * @property string $key
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ConfigOption $configOption
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
    /**
     * @use HasFactory<Factory>
     */
    use HasFactory;

    protected $table = 'config_option_values';

    protected $guarded = [];

    // Define the relationship with ConfigOption
    /**
     * @return BelongsTo<ConfigOption, $this>
     */
    public function configOption(): BelongsTo
    {
        return $this->belongsTo(ConfigOption::class, 'option_id');
    }
}
