<?php

declare(strict_types=1);

namespace App\Model\License;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string|null $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read LicensePermissionPivot|null $pivot
 * @property-read Collection<int, LicenseType> $licenseTypes
 * @property-read int|null $license_types_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermission whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LicensePermission extends Model
{
    protected $table = 'license_permissions';

    protected $fillable = ['id', 'permissions'];

    /**
     * @return BelongsToMany<LicenseType, $this, LicensePermissionPivot>
     */
    public function licenseTypes(): BelongsToMany
    {
        return $this->belongsToMany(LicenseType::class, 'license_license_permissions')
            ->using(LicensePermissionPivot::class)
            ->withTimestamps();
    }
}
