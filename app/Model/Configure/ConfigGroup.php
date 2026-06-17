<?php

declare(strict_types=1);

namespace App\Model\Configure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $config_group_name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\Configure\ConfigOption> $configOptions
 * @property-read int|null $config_options_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup whereConfigGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ConfigGroup whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ConfigGroup extends Model
{
    use HasFactory;

    protected $table = 'config_group';

    protected $guarded = [];

    // Define the relationship with ConfigOption
    public function configOptions()
    {
        return $this->hasMany(ConfigOption::class, 'group_id');
    }
}
