<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $option_key
 * @property string|null $option_value
 * @property string|null $option_group
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption group(mixed $group)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereOptionGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereOptionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseOption whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LicenseOption extends Model
{
    protected $table = 'license_options';

    protected $fillable = [
        'option_key',
        'option_value',
        'option_group',
    ];

    /**
     * @param  Builder<Model>  $query
     */
    #[Scope]
    protected function group(Builder $query, mixed $group): mixed
    {
        return $query->where('option_group', $group);
    }

    public static function getValue(string $key, ?string $group = null): ?string
    {
        $query = self::where('option_key', $key);
        if ($group !== null) {
            $query->where('option_group', $group);
        }

        return $query->value('option_value');
    }

    public static function setValue(string $key, string $value, ?string $group = null): void
    {
        self::updateOrCreate(
            ['option_key' => $key, 'option_group' => $group],
            ['option_value' => $value]
        );
    }
}
