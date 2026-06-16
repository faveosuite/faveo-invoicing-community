<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;

class LicenseOption extends Model
{
    protected $table = 'license_options';

    protected $fillable = [
        'option_key',
        'option_value',
        'option_group',
    ];

    #[Scope]
    protected function group($query, $group)
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
