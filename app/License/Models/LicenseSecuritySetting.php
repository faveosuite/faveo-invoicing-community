<?php

declare(strict_types=1);

namespace App\License\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Single-row auto-ban settings (id=1): whether it's enabled, and how many
 * failed licensing attempts (per IP) trigger an auto-ban.
 *
 * @property int $id
 * @property bool $auto_ban_enabled
 * @property int $failed_licensings_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSecuritySetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSecuritySetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicenseSecuritySetting query()
 *
 * @mixin \Eloquent
 */
class LicenseSecuritySetting extends Model
{
    protected $table = 'license_security_settings';

    protected $fillable = [
        'auto_ban_enabled',
        'failed_licensings_limit',
    ];

    protected function casts(): array
    {
        return [
            'auto_ban_enabled' => 'boolean',
            'failed_licensings_limit' => 'integer',
        ];
    }
}
