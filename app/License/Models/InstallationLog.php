<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Installation log — tracks installation activity with license code.
 * Columns match the original license app's installation_logs table.
 */
class InstallationLog extends Model
{
    protected $table = 'installation_logs';

    protected $fillable = [
        'license_code',
        'version_number',
        'installation_ip',
        'installation_domain',
        'installation_last_active_date',
        'installation_status',
    ];

    /**
     * Scope: filter by license code.
     */
    #[Scope]
    protected function forLicense($query, string $licenseCode)
    {
        return $query->where('license_code', $licenseCode);
    }

    /**
     * Scope: order by most recent activity.
     */
    #[Scope]
    protected function recent($query)
    {
        return $query->orderBy('installation_last_active_date', 'desc');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'installation_last_active_date' => 'datetime',
            'installation_status' => 'integer',
        ];
    }
}
