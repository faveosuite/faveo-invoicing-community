<?php

namespace App\Model\License;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LicensePermissionPivot extends Pivot
{
    use SystemActivityLogsTrait;

    protected $table = 'license_license_permissions';

    protected $fillable = ['license_type_id', 'license_permission_id', 'status'];

    protected $logName = 'license_permission';

    protected $logNameColumn = 'license_permission_id';

    protected $logAttributes = [
        'license_permission_id', 'status',
    ];

    protected $logUrl = [
        'segments' => ['license-permissions'],
    ];

    protected function getMappings(): array
    {
        return [
            'license_permission_id' => ['License Permission', fn ($value) => LicensePermission::find($value)?->permissions],
        ];
    }

    public function getLogNameColumn()
    {
        $licenseType = LicenseType::find($this->license_type_id);
        $permission = LicensePermission::find($this->license_permission_id);

        return $licenseType?->name.' ('.$permission?->permissions.')'
            ?? $this->license_permission_id;
    }
}
