<?php

declare(strict_types=1);

namespace App\Model\License;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string|null $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Model\License\LicensePermissionPivot|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Model\License\LicenseType> $licenseTypes
 * @property-read int|null $license_types_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LicensePermission extends Model
{
    protected $table = 'license_permissions';

    protected $fillable = ['id', 'permissions'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<LicenseType, $this, LicensePermissionPivot>
     */
    public function licenseTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(LicenseType::class, 'license_license_permissions')
            ->using(LicensePermissionPivot::class)
            ->withTimestamps();
    }
}
