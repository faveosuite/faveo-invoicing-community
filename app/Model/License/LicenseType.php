<?php

namespace App\Model\License;

use App\Traits\SystemActivityLogsTrait;
use Illuminate\Database\Eloquent\Model;

class LicenseType extends Model
{
    use SystemActivityLogsTrait;

    protected $table = 'license_types';

    protected $fillable = ['id', 'name'];


    protected $logName = 'license_types';

    protected $logNameColumn = 'Settings';


    protected $logAttributes = [
        'id', 'name'
    ];

    protected $logUrl = ['license-type'];

    protected function getMappings(): array
    {
        return [
            'name' => ['License Type', fn ($value) => $value],
        ];
    }

    public function permissions()
    {
        return $this->belongsToMany(LicensePermission::class, 'license_license_permissions')->withTimestamps();
    }

    public function products()
    {
        return $this->hasMany(\App\Model\Product\Product::class, 'type');
    }

    public function delete()
    {
        $this->permissions()->detach();
        $this->products()->delete();

        return parent::delete();
    }
}
