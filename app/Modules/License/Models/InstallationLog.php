<?php

namespace App\Modules\License\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
        'installation_last_active_date' => 'datetime',
        'installation_status' => 'integer',
    ];

    /**
     * Scope: filter by license code.
     */
    public function scopeForLicense($query, string $licenseCode)
    {
        return $query->where('license_code', $licenseCode);
    }

    /**
     * Scope: order by most recent activity.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('installation_last_active_date', 'desc');
    }
}
