<?php

namespace App\Model\License;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $license_type_id
 * @property int $license_permission_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activitiesAsSubject
 * @property-read int|null $activities_as_subject_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot whereLicensePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot whereLicenseTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LicensePermissionPivot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LicensePermissionPivot extends Pivot
{
    use SystemActivityLogsTrait;

    protected $table = 'license_license_permissions';

    protected $fillable = ['license_type_id', 'license_permission_id', 'status'];

    protected string $logName = 'license_permission';

    protected string $logNameColumn = 'license_permission_id';

    /**
     * @var array<mixed>
     */
    protected array $logAttributes = [
        'license_permission_id', 'status',
    ];

    /**
     * @var array<mixed>
     */
    protected array $logUrl = [
        'segments' => ['license-permissions'],
    ];

    /**
     * @return array<mixed>
     */
    protected function getMappings(): array
    {
        return [
            'license_permission_id' => ['License Permission', fn ($value) => LicensePermission::find($value)?->permissions], // @phpstan-ignore property.notFound
        ];
    }

    public function getLogNameColumn(): string
    {
        $licenseType = LicenseType::find($this->license_type_id);
        $permission = LicensePermission::find($this->license_permission_id);

        return $licenseType?->name.' ('.$permission?->permissions.')' // @phpstan-ignore nullCoalesce.expr
            ?? $this->license_permission_id;
    }
}
