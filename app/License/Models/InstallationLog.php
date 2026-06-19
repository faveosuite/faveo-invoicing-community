<?php

namespace App\License\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * Installation log — tracks installation activity with license code.
 *
 * Columns match the original license app's installation_logs table.
 *
 * @property int $id
 * @property string $license_code
 * @property string|null $version_number
 * @property string|null $installation_ip
 * @property string|null $installation_domain
 * @property \Illuminate\Support\Carbon|null $installation_last_active_date
 * @property int $installation_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog forLicense(string $licenseCode)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog recent()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereInstallationDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereInstallationIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereInstallationLastActiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereInstallationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereLicenseCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallationLog whereVersionNumber($value)
 *
 * @mixin \Eloquent
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
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    #[Scope]
    protected function forLicense(\Illuminate\Database\Eloquent\Builder $query, string $licenseCode): mixed
    {
        return $query->where('license_code', $licenseCode);
    }

    /**
     * Scope: order by most recent activity.
     */
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    #[Scope]
    protected function recent(\Illuminate\Database\Eloquent\Builder $query): mixed
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
