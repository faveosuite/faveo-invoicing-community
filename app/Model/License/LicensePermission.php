<?php

namespace App\Model\License;

use Illuminate\Database\Eloquent\Model;

class LicensePermission extends Model
{
    protected $table = 'license_permissions';

    protected $fillable = ['id', 'permissions'];

    public function licenseTypes()
    {
        return $this->belongsToMany(LicenseType::class, 'license_license_permissions')->withTimestamps();
    }
}
